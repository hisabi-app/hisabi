<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init the finance app';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->writeLogo();

        $name = $this->ask('What is your name?');
        $email = $this->ask('What is your email address?');
        $password = $this->secret('Please provide a new password');

        if(empty($name) || empty($email) || empty($password)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS </> 😳 \n");
            $this->error('Please provide the required information!');
            return;
        }

        if ($this->confirm('Do you wish to generate the default brands and categories?', true)) {
            $this->call('db:seed');
        }

        User::create(['name' => $name, 'email' => $email, 'password' => bcrypt($password)]);

        $this->line("<options=bold,reverse;fg=green> User created with email {$email} </> 🤙\n");

        $this->writeWelcomeMessage();

        return 0;
    }

    public function writeLogo()
    {
        $asciiLogo = <<<EOT
        $$$$$$$$\ $$$$$$\ $$\   $$\  $$$$$$\  $$\   $$\  $$$$$$\  $$$$$$$$\ 
        $$  _____|\_$$  _|$$$\  $$ |$$  __$$\ $$$\  $$ |$$  __$$\ $$  _____|
        $$ |        $$ |  $$$$\ $$ |$$ /  $$ |$$$$\ $$ |$$ /  \__|$$ |      
        $$$$$\      $$ |  $$ $$\$$ |$$$$$$$$ |$$ $$\$$ |$$ |      $$$$$\    
        $$  __|     $$ |  $$ \$$$$ |$$  __$$ |$$ \$$$$ |$$ |      $$  __|   
        $$ |        $$ |  $$ |\$$$ |$$ |  $$ |$$ |\$$$ |$$ |  $$\ $$ |      
        $$ |      $$$$$$\ $$ | \$$ |$$ |  $$ |$$ | \$$ |\$$$$$$  |$$$$$$$$\ 
        \__|      \______|\__|  \__|\__|  \__|\__|  \__| \______/ \________|          
EOT;

        $this->line("\n".$asciiLogo."\n");
    }
    
    public function writeWelcomeMessage()
    {
        if ($this->confirm('Would you like to show some love by supporting this project?')) {
            if(PHP_OS_FAMILY == 'Darwin') exec('open https://github.com/saleem-hadad/finance');
            if(PHP_OS_FAMILY == 'Windows') exec('start https://github.com/saleem-hadad/finance');
            if(PHP_OS_FAMILY == 'Linux') exec('xdg-open https://github.com/saleem-hadad/finance');

            $this->line("Thanks! Means the world to me!");
        }
    }
}
