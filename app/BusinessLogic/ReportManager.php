<?php

namespace App\BusinessLogic;

use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use App\Contracts\ReportManager as ReportManagerContract;

class ReportManager implements ReportManagerContract
{
    protected $data = [];
    private Carbon $startDateModel;
    private Carbon $endDateModel;
    private Carbon $startDatePrevMonthModel;
    private Carbon $endDatePrevMonthModel;

    public function generate($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now();
        $endDate = $endDate ?? now();

        $this->startDateModel = Carbon::parse($startDate)->startOfMonth();
        $this->endDateModel = Carbon::parse($endDate)->endOfMonth();
        $this->startDatePrevMonthModel = Carbon::parse($startDate)->subMonthNoOverflow()->startOfMonth();
        $this->endDatePrevMonthModel = Carbon::parse($endDate)->subMonthNoOverflow()->endOfMonth();

        $newBrands = Brand::whereBetween('created_at', [$this->startDateModel, $this->endDateModel])->pluck('name');

        $this->addSection('Overview', $this->getOverviewData());

        foreach(Category::all() as $category)
        {
            $brandsData = [];

            foreach($category->brands as $brand) {
                $totalCurrentMonth = $brand->transactions()->whereBetween('created_at', [$this->startDateModel, $this->endDateModel])->sum('amount');
                $totalLastMonth = $brand->transactions()->whereBetween('created_at', [$this->startDatePrevMonthModel, $this->endDatePrevMonthModel])->sum('amount');
                $change = ! $totalLastMonth ? '-' : number_format(($totalCurrentMonth / $totalLastMonth - 1) * 100, 2);

                if($totalCurrentMonth == 0 && $totalLastMonth == 0) {
                    continue;
                }

                $brandsData[] = [
                    'name' => $brand->name,
                    'total_current_month' => $totalCurrentMonth,
                    'total_previous_month' => $totalLastMonth,
                    'change' => $change,
                    'change_color' => $this->getChangeColor($change, $category->type),
                    'is_new' => $newBrands->contains($brand->name)
                ];
            }

            $brandsData = $this->calculateAndAddAllBrandsData($brandsData, $category);

            // issue with rendering big row using Dompdf library,
            // the workaround is to split the list of brands to
            // multiple sections that each one can fit in page.
            $this->splitBrandListIntoPages($brandsData, $category);
        }

        return $this->data;
    }

    protected function splitBrandListIntoPages($brandsData, $category)
    {
        if(count($brandsData) == 1) {
            return;
        }

        if(count(array_chunk($brandsData, 25)) > 1) {
            foreach(array_chunk($brandsData, 25) as $index => $chunk) {
                $this->addSection($category->name . "-" . $index + 1, $chunk);
            }
            return;
        }

        $this->addSection($category->name, $brandsData);
    }

    protected function addSection($sectionName, $data)
    {
        $this->data[$sectionName] = $data;
    }

    protected function getChangeColor($change, $type)
    {
        if($change == '-') {
            return 'gray';
        }

        if($type == Category::INCOME) {
            return $change >= 0 ? 'green' : 'red';
        }

        return $change >= 0 ? 'red' : 'green';
    }

    protected function calculateAndAddAllBrandsData($brandsData, $category)
    {
        $allCurrentMonth = array_reduce($brandsData, function ($carry, $item) {
            $carry += $item['total_current_month'];

            return $carry;
        });

        $allLastMonth = array_reduce($brandsData, function ($carry, $item) {
            $carry += $item['total_previous_month'];

            return $carry;
        });

        $change = ! $allLastMonth ? '-' : number_format(($allCurrentMonth / $allLastMonth - 1) * 100, 2);

        return array_merge([[
            'name' => 'All',
            'total_current_month' => $allCurrentMonth,
            'total_previous_month' => $allLastMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, $category->type)
        ]], $brandsData);
    }

    protected function getOverviewData()
    {
        return [
            $this->getTotalCash(),
            $this->getTotalIncome(),
            $this->getTotalExpenses(),
            $this->getTotalInvestment(),
            $this->getTotalSavings(),
        ];
    }

    protected function getTotalCash()
    {
        $totalIncome = Transaction::income()->where('created_at', '<', $this->endDateModel)->sum('amount');
        $totalExpenses = Transaction::expenses()->where('created_at', '<', $this->endDateModel)->sum('amount');
        $totalInvestment = Transaction::investment()->where('created_at', '<', $this->endDateModel)->sum('amount');
        $totalSavings = Transaction::savings()->where('created_at', '<', $this->endDateModel)->sum('amount');

        $totalIncomeExcludingThisMonth = Transaction::income()->where('created_at', '<', $this->startDateModel)->sum('amount');
        $totalExpensesExcludingThisMonth = Transaction::expenses()->where('created_at', '<', $this->startDateModel)->sum('amount');
        $totalInvestmentExcludingThisMonth = Transaction::investment()->where('created_at', '<', $this->startDateModel)->sum('amount');
        $totalSavingsExcludingThisMonth = Transaction::savings()->where('created_at', '<', $this->startDateModel)->sum('amount');

        $totalCashTillNow = $totalIncome - ($totalExpenses + $totalInvestment + $totalSavings);
        $totalCashExcludingThisMonth = $totalIncomeExcludingThisMonth - ($totalExpensesExcludingThisMonth + $totalInvestmentExcludingThisMonth + $totalSavingsExcludingThisMonth);

        $change = ! $totalCashExcludingThisMonth ? '-' : number_format(($totalCashTillNow / $totalCashExcludingThisMonth - 1) * 100, 2);

        return [
            'name' => 'Total Cash',
            'total_current_month' => $totalCashTillNow,
            'total_previous_month' => $totalCashExcludingThisMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, 'INCOME')
        ];
    }

    protected function getTotalIncome()
    {
        $total = Transaction::income()->whereBetween('created_at', [$this->startDateModel, $this->endDateModel])->sum('amount');
        $totalExcludingThisMonth = Transaction::income()->whereBetween('created_at', [$this->startDatePrevMonthModel, $this->endDatePrevMonthModel])->sum('amount');

        $change = ! $totalExcludingThisMonth ? '-' : number_format(($total / $totalExcludingThisMonth - 1) * 100, 2);

        return [
            'name' => 'Total Income',
            'total_current_month' => $total,
            'total_previous_month' => $totalExcludingThisMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, 'INCOME')
        ];
    }

    protected function getTotalExpenses()
    {
        $total = Transaction::expenses()->whereBetween('created_at', [$this->startDateModel, $this->endDateModel])->sum('amount');
        $totalExcludingThisMonth = Transaction::expenses()->whereBetween('created_at', [$this->startDatePrevMonthModel, $this->endDatePrevMonthModel])->sum('amount');

        $change = ! $totalExcludingThisMonth ? '-' : number_format(($total / $totalExcludingThisMonth - 1) * 100, 2);

        return [
            'name' => 'Total Expenses',
            'total_current_month' => $total,
            'total_previous_month' => $totalExcludingThisMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, 'EXPENSES')
        ];
    }

    protected function getTotalInvestment()
    {
        $total = Transaction::investment()->whereBetween('created_at', [$this->startDateModel, $this->endDateModel])->sum('amount');
        $totalExcludingThisMonth = Transaction::investment()->whereBetween('created_at', [$this->startDatePrevMonthModel, $this->endDatePrevMonthModel])->sum('amount');

        $change = ! $totalExcludingThisMonth ? '-' : number_format(($total / $totalExcludingThisMonth - 1) * 100, 2);

        return [
            'name' => 'Total Investment',
            'total_current_month' => $total,
            'total_previous_month' => $totalExcludingThisMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, 'INCOME')
        ];
    }

    protected function getTotalSavings()
    {
        $total = Transaction::savings()->whereBetween('created_at', [$this->startDateModel, $this->endDateModel])->sum('amount');
        $totalExcludingThisMonth = Transaction::savings()->whereBetween('created_at', [$this->startDatePrevMonthModel, $this->endDatePrevMonthModel])->sum('amount');

        $change = ! $totalExcludingThisMonth ? '-' : number_format(($total / $totalExcludingThisMonth - 1) * 100, 2);

        return [
            'name' => 'Total Savings',
            'total_current_month' => $total,
            'total_previous_month' => $totalExcludingThisMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, 'INCOME')
        ];
    }
}
