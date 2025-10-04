<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories with brands
        $income = $this->createIncome();
        $housing = $this->createHousing();
        $groceries = $this->createGroceries();
        $dining = $this->createDining();
        $transportation = $this->createTransportation();
        $utilities = $this->createUtilities();
        $shopping = $this->createShopping();
        $entertainment = $this->createEntertainment();
        $healthcare = $this->createHealthcare();
        $personalCare = $this->createPersonalCare();
        $savings = $this->createSavings();
        $investment = $this->createInvestment();

        // Generate transactions for the past 2 years (24 months)
        $this->generateTransactions($income, $housing, $groceries, $dining, $transportation, $utilities, $shopping, $entertainment, $healthcare, $personalCare, $savings, $investment);
    }

    private function createIncome(): Category
    {
        $category = Category::create([
            'name' => 'Income',
            'type' => Category::INCOME,
            'color' => 'green',
        ]);

        $category->brands()->createMany([
            ['name' => 'Monthly Salary'],
            ['name' => 'Bonus'],
            ['name' => 'Freelance Income'],
        ]);

        return $category;
    }

    private function createHousing(): Category
    {
        $category = Category::create([
            'name' => 'Housing',
            'type' => Category::EXPENSES,
            'color' => 'blue',
        ]);

        $category->brands()->createMany([
            ['name' => 'Apartment Rent'],
            ['name' => 'DEWA'],
            ['name' => 'Empower Chiller'],
            ['name' => 'Property Management'],
        ]);

        return $category;
    }

    private function createGroceries(): Category
    {
        $category = Category::create([
            'name' => 'Groceries',
            'type' => Category::EXPENSES,
            'color' => 'emerald',
        ]);

        $category->brands()->createMany([
            ['name' => 'Carrefour'],
            ['name' => 'Lulu Hypermarket'],
            ['name' => 'Spinneys'],
            ['name' => 'Union Coop'],
            ['name' => 'West Zone'],
            ['name' => 'Choithrams'],
            ['name' => 'Waitrose'],
        ]);

        return $category;
    }

    private function createDining(): Category
    {
        $category = Category::create([
            'name' => 'Dining & Restaurants',
            'type' => Category::EXPENSES,
            'color' => 'orange',
        ]);

        $category->brands()->createMany([
            ['name' => 'Starbucks'],
            ['name' => 'Costa Coffee'],
            ['name' => 'McDonald\'s'],
            ['name' => 'KFC'],
            ['name' => 'Burger King'],
            ['name' => 'Subway'],
            ['name' => 'Tim Hortons'],
            ['name' => 'Shake Shack'],
            ['name' => 'Five Guys'],
            ['name' => 'Texas Roadhouse'],
            ['name' => 'Chili\'s'],
            ['name' => 'Applebee\'s'],
            ['name' => 'Paul Bakery'],
            ['name' => 'Cafe Bateel'],
            ['name' => 'Operation Falafel'],
            ['name' => 'Al Mallah'],
            ['name' => 'Zomato'],
            ['name' => 'Talabat'],
            ['name' => 'Deliveroo'],
        ]);

        return $category;
    }

    private function createTransportation(): Category
    {
        $category = Category::create([
            'name' => 'Transportation',
            'type' => Category::EXPENSES,
            'color' => 'purple',
        ]);

        $category->brands()->createMany([
            ['name' => 'ENOC'],
            ['name' => 'ADNOC'],
            ['name' => 'EPPCO'],
            ['name' => 'Salik'],
            ['name' => 'RTA'],
            ['name' => 'Uber'],
            ['name' => 'Careem'],
            ['name' => 'Dubai Metro'],
            ['name' => 'Car Insurance'],
            ['name' => 'Car Service'],
        ]);

        return $category;
    }

    private function createUtilities(): Category
    {
        $category = Category::create([
            'name' => 'Utilities & Telecom',
            'type' => Category::EXPENSES,
            'color' => 'cyan',
        ]);

        $category->brands()->createMany([
            ['name' => 'Etisalat'],
            ['name' => 'Du'],
            ['name' => 'Virgin Mobile'],
        ]);

        return $category;
    }

    private function createShopping(): Category
    {
        $category = Category::create([
            'name' => 'Shopping',
            'type' => Category::EXPENSES,
            'color' => 'pink',
        ]);

        $category->brands()->createMany([
            ['name' => 'Dubai Mall'],
            ['name' => 'Mall of the Emirates'],
            ['name' => 'IKEA'],
            ['name' => 'Home Centre'],
            ['name' => 'Centrepoint'],
            ['name' => 'H&M'],
            ['name' => 'Zara'],
            ['name' => 'Namshi'],
            ['name' => 'Noon'],
            ['name' => 'Amazon.ae'],
            ['name' => 'Sharaf DG'],
            ['name' => 'Jumbo Electronics'],
            ['name' => 'Virgin Megastore'],
            ['name' => 'Ace Hardware'],
        ]);

        return $category;
    }

    private function createEntertainment(): Category
    {
        $category = Category::create([
            'name' => 'Entertainment',
            'type' => Category::EXPENSES,
            'color' => 'red',
        ]);

        $category->brands()->createMany([
            ['name' => 'VOX Cinemas'],
            ['name' => 'Reel Cinemas'],
            ['name' => 'Netflix'],
            ['name' => 'OSN'],
            ['name' => 'Spotify'],
            ['name' => 'Dubai Parks & Resorts'],
            ['name' => 'IMG Worlds'],
            ['name' => 'Ski Dubai'],
            ['name' => 'Aquaventure'],
            ['name' => 'La Perle'],
        ]);

        return $category;
    }

    private function createHealthcare(): Category
    {
        $category = Category::create([
            'name' => 'Healthcare',
            'type' => Category::EXPENSES,
            'color' => 'rose',
        ]);

        $category->brands()->createMany([
            ['name' => 'Aster Clinic'],
            ['name' => 'Mediclinic'],
            ['name' => 'NMC Healthcare'],
            ['name' => 'Life Pharmacy'],
            ['name' => 'Aster Pharmacy'],
            ['name' => 'Boots Pharmacy'],
            ['name' => 'Health Insurance'],
        ]);

        return $category;
    }

    private function createPersonalCare(): Category
    {
        $category = Category::create([
            'name' => 'Personal Care',
            'type' => Category::EXPENSES,
            'color' => 'violet',
        ]);

        $category->brands()->createMany([
            ['name' => 'Fitness First'],
            ['name' => 'GymNation'],
            ['name' => 'Gents Salon'],
            ['name' => 'Ladies Salon'],
            ['name' => 'Spa'],
        ]);

        return $category;
    }

    private function createSavings(): Category
    {
        $category = Category::create([
            'name' => 'Savings',
            'type' => Category::SAVINGS,
            'color' => 'teal',
        ]);

        $category->brands()->createMany([
            ['name' => 'Emergency Fund'],
            ['name' => 'Vacation Fund'],
            ['name' => 'Home Down Payment'],
            ['name' => 'Car Fund'],
        ]);

        return $category;
    }

    private function createInvestment(): Category
    {
        $category = Category::create([
            'name' => 'Investments',
            'type' => Category::INVESTMENT,
            'color' => 'indigo',
        ]);

        $category->brands()->createMany([
            ['name' => 'Stocks - DFM'],
            ['name' => 'Stocks - NASDAQ'],
            ['name' => 'Cryptocurrency'],
            ['name' => 'Mutual Funds'],
            ['name' => 'Gold Investment'],
            ['name' => 'Real Estate Investment'],
        ]);

        return $category;
    }

    private function generateTransactions(
        Category $income,
        Category $housing,
        Category $groceries,
        Category $dining,
        Category $transportation,
        Category $utilities,
        Category $shopping,
        Category $entertainment,
        Category $healthcare,
        Category $personalCare,
        Category $savings,
        Category $investment
    ): void {
        // Generate for the past 2 years (24 months)
        for ($monthsAgo = 23; $monthsAgo >= 0; $monthsAgo--) {
            $startOfMonth = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonths($monthsAgo)->endOfMonth();

            $this->generateMonthlyTransactions(
                $startOfMonth,
                $endOfMonth,
                $monthsAgo,
                $income,
                $housing,
                $groceries,
                $dining,
                $transportation,
                $utilities,
                $shopping,
                $entertainment,
                $healthcare,
                $personalCare,
                $savings,
                $investment
            );
        }
    }

    private function generateMonthlyTransactions(
        Carbon $startOfMonth,
        Carbon $endOfMonth,
        int $monthsAgo,
        Category $income,
        Category $housing,
        Category $groceries,
        Category $dining,
        Category $transportation,
        Category $utilities,
        Category $shopping,
        Category $entertainment,
        Category $healthcare,
        Category $personalCare,
        Category $savings,
        Category $investment
    ): void {
        // Income - Monthly salary (1st of each month)
        // Base salary set to ensure positive cash flow after expenses, savings, and investments
        // AED 32,000/month provides buffer for months with large annual expenses (insurance, etc.)
        $this->createTransaction(
            $income->brands()->where('name', 'Monthly Salary')->first(),
            27000,
            $startOfMonth->copy()->addDay(),
            'Monthly salary deposit'
        );

        // Housing - Rent (5th of each month)
        $this->createTransaction(
            $housing->brands()->where('name', 'Apartment Rent')->first(),
            5500,
            $startOfMonth->copy()->addDays(4),
            'Monthly apartment rent'
        );

        // Housing - DEWA (around 10th of each month)
        $this->createTransaction(
            $housing->brands()->where('name', 'DEWA')->first(),
            rand(350, 550),
            $startOfMonth->copy()->addDays(9),
            'Electricity and water bill'
        );

        // Housing - Chiller (around 15th of each month)
        $this->createTransaction(
            $housing->brands()->where('name', 'Empower Chiller')->first(),
            rand(400, 600),
            $startOfMonth->copy()->addDays(14),
            'Chiller charges'
        );

        // Utilities - Mobile (around 12th of each month)
        $etisalat = $utilities->brands()->where('name', 'Etisalat')->first();
        $this->createTransaction(
            $etisalat,
            199,
            $startOfMonth->copy()->addDays(11),
            'Mobile plan'
        );

        // Utilities - Internet (same day as mobile)
        $this->createTransaction(
            $etisalat,
            299,
            $startOfMonth->copy()->addDays(11),
            'Home internet'
        );

        $this->generateGroceryTransactions($groceries, $startOfMonth, $endOfMonth);
        $this->generateDiningTransactions($dining, $startOfMonth, $endOfMonth);
        $this->generateTransportationTransactions($transportation, $startOfMonth, $endOfMonth, $monthsAgo);
        $this->generateShoppingTransactions($shopping, $startOfMonth, $endOfMonth);
        $this->generateEntertainmentTransactions($entertainment, $startOfMonth, $endOfMonth, $monthsAgo);
        $this->generatePersonalCareTransactions($personalCare, $startOfMonth);
        $this->generateHealthcareTransactions($healthcare, $startOfMonth, $monthsAgo);
        $this->generateSavingsTransactions($savings, $startOfMonth, $monthsAgo);
        $this->generateInvestmentTransactions($investment, $startOfMonth, $monthsAgo);
        $this->generateSpecialTransactions($income, $transportation, $shopping, $entertainment, $startOfMonth, $monthsAgo);
    }

    private function generateGroceryTransactions(Category $groceries, Carbon $startOfMonth, Carbon $endOfMonth): void
    {
        $groceryBrands = $groceries->brands()->pluck('id', 'name');

        // Weekly shopping (4 times a month)
        for ($week = 0; $week < 4; $week++) {
            $day = $startOfMonth->copy()->addDays(7 * $week + rand(1, 6));
            if ($day <= $endOfMonth) {
                $brandChoice = ['Carrefour', 'Lulu Hypermarket', 'Spinneys', 'Union Coop'][rand(0, 3)];
                $this->createTransaction(
                    Brand::find($groceryBrands[$brandChoice]),
                    rand(250, 550),
                    $day,
                    'Weekly grocery shopping'
                );
            }
        }

        // Small purchases (8-10 times a month)
        for ($i = 0; $i < rand(8, 10); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['Carrefour', 'Spinneys', 'West Zone', 'Choithrams'][rand(0, 3)];
            $this->createTransaction(
                Brand::find($groceryBrands[$brandChoice]),
                rand(30, 120),
                $day,
                'Quick grocery run'
            );
        }
    }

    private function generateDiningTransactions(Category $dining, Carbon $startOfMonth, Carbon $endOfMonth): void
    {
        $diningBrands = $dining->brands()->pluck('id', 'name');

        // Coffee (3-4 times a week)
        $coffeeDays = rand(12, 16);
        for ($i = 0; $i < $coffeeDays; $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['Starbucks', 'Costa Coffee', 'Tim Hortons', 'Cafe Bateel'][rand(0, 3)];
            $this->createTransaction(
                Brand::find($diningBrands[$brandChoice]),
                rand(18, 45),
                $day
            );
        }

        // Fast food (2-3 times a week)
        for ($i = 0; $i < rand(8, 12); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['McDonald\'s', 'KFC', 'Burger King', 'Subway', 'Shake Shack'][rand(0, 4)];
            $this->createTransaction(
                Brand::find($diningBrands[$brandChoice]),
                rand(35, 85),
                $day
            );
        }

        // Restaurants (2-3 times a month)
        for ($i = 0; $i < rand(2, 3); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['Texas Roadhouse', 'Chili\'s', 'Applebee\'s', 'Operation Falafel'][rand(0, 3)];
            $this->createTransaction(
                Brand::find($diningBrands[$brandChoice]),
                rand(120, 280),
                $day,
                'Dinner out'
            );
        }

        // Food delivery (4-6 times a month)
        for ($i = 0; $i < rand(4, 6); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['Zomato', 'Talabat', 'Deliveroo'][rand(0, 2)];
            $this->createTransaction(
                Brand::find($diningBrands[$brandChoice]),
                rand(45, 95),
                $day,
                'Food delivery'
            );
        }
    }

    private function generateTransportationTransactions(Category $transportation, Carbon $startOfMonth, Carbon $endOfMonth, int $monthsAgo): void
    {
        $transportBrands = $transportation->brands()->pluck('id', 'name');

        // Fuel (2-3 times a month)
        for ($i = 0; $i < rand(2, 3); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['ENOC', 'ADNOC', 'EPPCO'][rand(0, 2)];
            $this->createTransaction(
                Brand::find($transportBrands[$brandChoice]),
                rand(150, 250),
                $day,
                'Petrol refill'
            );
        }

        // Salik (multiple times)
        for ($i = 0; $i < rand(15, 25); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $this->createTransaction(
                Brand::find($transportBrands['Salik']),
                4,
                $day,
                'Toll gate'
            );
        }

        // Ride hailing (3-5 times a month)
        for ($i = 0; $i < rand(3, 5); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['Uber', 'Careem'][rand(0, 1)];
            $this->createTransaction(
                Brand::find($transportBrands[$brandChoice]),
                rand(25, 65),
                $day,
                'Ride to destination'
            );
        }

        // Metro (5-10 times a month)
        for ($i = 0; $i < rand(5, 10); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $this->createTransaction(
                Brand::find($transportBrands['Dubai Metro']),
                rand(4, 8),
                $day,
                'Metro ride'
            );
        }
    }

    private function generateShoppingTransactions(Category $shopping, Carbon $startOfMonth, Carbon $endOfMonth): void
    {
        $shoppingBrands = $shopping->brands()->pluck('id', 'name');

        // Random purchases (2-4 times a month)
        for ($i = 0; $i < rand(2, 4); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brands = ['Amazon.ae', 'Noon', 'Namshi', 'H&M', 'Zara', 'Sharaf DG', 'Jumbo Electronics'];
            $brandChoice = $brands[rand(0, count($brands) - 1)];
            $this->createTransaction(
                Brand::find($shoppingBrands[$brandChoice]),
                rand(150, 650),
                $day
            );
        }
    }

    private function generateEntertainmentTransactions(Category $entertainment, Carbon $startOfMonth, Carbon $endOfMonth, int $monthsAgo): void
    {
        $entertainmentBrands = $entertainment->brands()->pluck('id', 'name');

        // Streaming services (once a month)
        $this->createTransaction(
            Brand::find($entertainmentBrands['Netflix']),
            56,
            $startOfMonth->copy()->addDays(rand(1, 10)),
            'Netflix subscription'
        );
        $this->createTransaction(
            Brand::find($entertainmentBrands['Spotify']),
            19.99,
            $startOfMonth->copy()->addDays(rand(1, 10)),
            'Spotify Premium'
        );

        // Cinema (1-2 times a month)
        for ($i = 0; $i < rand(1, 2); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $endOfMonth->day));
            $brandChoice = ['VOX Cinemas', 'Reel Cinemas'][rand(0, 1)];
            $this->createTransaction(
                Brand::find($entertainmentBrands[$brandChoice]),
                rand(45, 85),
                $day,
                'Movie tickets'
            );
        }
    }

    private function generatePersonalCareTransactions(Category $personalCare, Carbon $startOfMonth): void
    {
        $personalCareBrands = $personalCare->brands()->pluck('id', 'name');

        // Gym (once a month, around 1st)
        $this->createTransaction(
            Brand::find($personalCareBrands['Fitness First']),
            299,
            $startOfMonth->copy()->addDays(rand(1, 5)),
            'Gym membership'
        );

        // Salon/Barber (once a month)
        $salonChoice = rand(0, 1) ? 'Gents Salon' : 'Ladies Salon';
        $this->createTransaction(
            Brand::find($personalCareBrands[$salonChoice]),
            $salonChoice === 'Gents Salon' ? rand(35, 70) : rand(150, 350),
            $startOfMonth->copy()->addDays(rand(10, 25)),
            'Haircut'
        );
    }

    private function generateHealthcareTransactions(Category $healthcare, Carbon $startOfMonth, int $monthsAgo): void
    {
        $healthcareBrands = $healthcare->brands()->pluck('id', 'name');

        // Pharmacy (1-3 times a month)
        for ($i = 0; $i < rand(1, 3); $i++) {
            $day = $startOfMonth->copy()->addDays(rand(1, $startOfMonth->daysInMonth));
            $brandChoice = ['Life Pharmacy', 'Aster Pharmacy', 'Boots Pharmacy'][rand(0, 2)];
            $this->createTransaction(
                Brand::find($healthcareBrands[$brandChoice]),
                rand(35, 150),
                $day,
                'Medicine and health products'
            );
        }

        // Health Insurance (quarterly, so only every 3 months)
        if ($monthsAgo % 3 === 0) {
            $this->createTransaction(
                Brand::find($healthcareBrands['Health Insurance']),
                1200,
                $startOfMonth->copy()->addDays(5),
                'Quarterly health insurance premium'
            );
        }
    }

    private function generateSavingsTransactions(Category $savings, Carbon $startOfMonth, int $monthsAgo): void
    {
        $savingsBrands = $savings->brands()->pluck('id', 'name');

        // Monthly emergency fund savings (around 5th of each month, after salary)
        $this->createTransaction(
            Brand::find($savingsBrands['Emergency Fund']),
            800,
            $startOfMonth->copy()->addDays(5),
            'Monthly emergency fund'
        );

        // Vacation fund (every month)
        $this->createTransaction(
            Brand::find($savingsBrands['Vacation Fund']),
            400,
            $startOfMonth->copy()->addDays(5),
            'Vacation savings'
        );

        // Home down payment (every 2 months)
        if ($monthsAgo % 2 === 0) {
            $this->createTransaction(
                Brand::find($savingsBrands['Home Down Payment']),
                1500,
                $startOfMonth->copy()->addDays(6),
                'Saving for home down payment'
            );
        }

        // Car fund (every 3 months)
        if ($monthsAgo % 3 === 0) {
            $this->createTransaction(
                Brand::find($savingsBrands['Car Fund']),
                1000,
                $startOfMonth->copy()->addDays(6),
                'Saving for new car'
            );
        }
    }

    private function generateInvestmentTransactions(Category $investment, Carbon $startOfMonth, int $monthsAgo): void
    {
        $investmentBrands = $investment->brands()->pluck('id', 'name');

        // Monthly stock investments - DFM (every month, smaller amounts)
        $this->createTransaction(
            Brand::find($investmentBrands['Stocks - DFM']),
            rand(400, 800),
            $startOfMonth->copy()->addDays(rand(7, 15)),
            'Dubai Financial Market stocks'
        );

        // NASDAQ stocks (every 2 months)
        if ($monthsAgo % 2 === 0) {
            $this->createTransaction(
                Brand::find($investmentBrands['Stocks - NASDAQ']),
                rand(800, 1500),
                $startOfMonth->copy()->addDays(rand(7, 15)),
                'US tech stocks investment'
            );
        }

        // Cryptocurrency (60% chance each month)
        if (rand(0, 10) > 4) {
            $this->createTransaction(
                Brand::find($investmentBrands['Cryptocurrency']),
                rand(200, 500),
                $startOfMonth->copy()->addDays(rand(5, 25)),
                'Crypto investment'
            );
        }

        // Mutual funds (quarterly)
        if ($monthsAgo % 3 === 0) {
            $this->createTransaction(
                Brand::find($investmentBrands['Mutual Funds']),
                rand(1500, 2500),
                $startOfMonth->copy()->addDays(rand(10, 20)),
                'Quarterly mutual fund contribution'
            );
        }

        // Gold investment (every 4 months)
        if ($monthsAgo % 4 === 0) {
            $this->createTransaction(
                Brand::find($investmentBrands['Gold Investment']),
                rand(1000, 2000),
                $startOfMonth->copy()->addDays(rand(10, 20)),
                'Gold purchase'
            );
        }

        // Real estate investment (every 6 months)
        if ($monthsAgo % 6 === 0) {
            $this->createTransaction(
                Brand::find($investmentBrands['Real Estate Investment']),
                rand(2500, 4000),
                $startOfMonth->copy()->addDays(rand(15, 25)),
                'Real estate investment fund'
            );
        }
    }

    private function generateSpecialTransactions(
        Category $income,
        Category $transportation,
        Category $shopping,
        Category $entertainment,
        Carbon $startOfMonth,
        int $monthsAgo
    ): void {
        $shoppingBrands = $shopping->brands()->pluck('id', 'name');
        $transportBrands = $transportation->brands()->pluck('id', 'name');
        $entertainmentBrands = $entertainment->brands()->pluck('id', 'name');

        // Random home items (40% chance each month)
        if (rand(0, 10) > 6) {
            $this->createTransaction(
                Brand::find($shoppingBrands['IKEA']),
                rand(200, 800),
                $startOfMonth->copy()->addDays(rand(10, 25)),
                'Home items'
            );
        }

        // Car service (every 6 months)
        if ($monthsAgo % 6 === 0) {
            $this->createTransaction(
                Brand::find($transportBrands['Car Service']),
                rand(750, 1200),
                $startOfMonth->copy()->addDays(rand(10, 20)),
                'Car maintenance and service'
            );
        }

        // Car insurance (annually)
        if ($monthsAgo % 12 === 0 && $monthsAgo > 0) {
            $this->createTransaction(
                Brand::find($transportBrands['Car Insurance']),
                rand(2500, 3500),
                $startOfMonth->copy()->addDays(rand(5, 15)),
                'Annual car insurance renewal'
            );
        }

        // Annual bonus (once a year around month 10-11, typically 1-2 months salary)
        if ($monthsAgo % 12 === 11 || $monthsAgo % 12 === 10) {
            $this->createTransaction(
                $income->brands()->where('name', 'Bonus')->first(),
                rand(20000, 35000),
                $startOfMonth->copy()->addDays(rand(15, 25)),
                'Annual performance bonus'
            );
        }

        // Quarterly freelance income (every 3-4 months, not always)
        if ($monthsAgo % 4 === 0 && rand(0, 10) > 5) {
            $this->createTransaction(
                $income->brands()->where('name', 'Freelance Income')->first(),
                rand(3000, 6000),
                $startOfMonth->copy()->addDays(rand(5, 20)),
                'Freelance project payment'
            );
        }

        // Theme park or special entertainment (2-3 times a year)
        if ($monthsAgo % 4 === 0 && rand(0, 2) > 0) {
            $entertainmentPlaces = ['IMG Worlds', 'Ski Dubai', 'Aquaventure', 'La Perle'];
            $place = $entertainmentPlaces[rand(0, count($entertainmentPlaces) - 1)];
            $this->createTransaction(
                Brand::find($entertainmentBrands[$place]),
                rand(250, 450),
                $startOfMonth->copy()->addDays(rand(15, 28)),
                'Special outing - ' . $place
            );
        }

        // Major shopping during sales (Dubai Shopping Festival - Jan/Feb, Dubai Summer Surprises - July/Aug)
        $month = $startOfMonth->month;
        if (in_array($month, [1, 2, 7, 8])) {
            $this->createTransaction(
                Brand::find($shoppingBrands['Dubai Mall']),
                rand(800, 2000),
                $startOfMonth->copy()->addDays(rand(10, 25)),
                'Shopping festival purchase'
            );
        }
    }

    private function createTransaction(Brand $brand, float $amount, Carbon $date, ?string $note = null): void
    {
        Transaction::create([
            'brand_id' => $brand->id,
            'amount' => $amount,
            'note' => $note,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
