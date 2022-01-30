<?php

namespace App\BusinessLogic;

use App\Contracts\ReportManager as ReportManagerContract;
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Ranges\LastMonth;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;

class ReportManager implements ReportManagerContract
{
    protected $data = [];

    public function generate() 
    {
        $newBrands = Brand::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->pluck('name');

        $currentMonthRange = new CurrentMonth;
        $lastMonthRange = new LastMonth;

        $this->addSection('Overview', $this->getOverviewData());

        foreach(Category::all() as $category)
        {
            $brandsData = [];

            foreach($category->brands as $brand) {
                $totalCurrentMonth = $brand->transactions()->whereBetween('created_at', [$currentMonthRange->start(), $currentMonthRange->end()])->sum('amount');
                $totalLastMonth = $brand->transactions()->whereBetween('created_at', [$lastMonthRange->start(), $lastMonthRange->end()])->sum('amount');
                $change = ! $totalLastMonth ? '-' : number_format(($totalCurrentMonth / $totalLastMonth - 1) * 100, 2);

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

            $this->addSection($category->name, $brandsData);
        }

        return $this->data;
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
        ];
    }

    protected function getTotalCash()
    {
        $totalIncome = Transaction::income()->sum('amount');
        $totalExpenses = Transaction::expenses()->sum('amount');
        
        $totalIncomeExcludingThisMonth = Transaction::income()->where('created_at', '<', now()->startOfMonth())->sum('amount');
        $totalExpensesExcludingThisMonth = Transaction::expenses()->where('created_at', '<', now()->startOfMonth())->sum('amount');
        
        $totalCashTillNow = $totalIncome - $totalExpenses;
        $totalCashExcludingThisMonth = $totalIncomeExcludingThisMonth - $totalExpensesExcludingThisMonth;

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
        $total = Transaction::income()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        $totalExcludingThisMonth = Transaction::income()->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('amount');
        
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
        $total = Transaction::expenses()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        $totalExcludingThisMonth = Transaction::expenses()->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('amount');
        
        $change = ! $totalExcludingThisMonth ? '-' : number_format(($total / $totalExcludingThisMonth - 1) * 100, 2);
        
        return [
            'name' => 'Total Expenses',
            'total_current_month' => $total,
            'total_previous_month' => $totalExcludingThisMonth,
            'change' => $change,
            'change_color' => $this->getChangeColor($change, 'EXPENSES')
        ];
    }
}