<?php
namespace Rik0253\Moonphase;

use Illuminate\Support\ServiceProvider;

class MoonphaseServiceProvider extends ServiceProvider{

    public function boot(){

    }

    public function register(){
        $app = $this->app ?: app();
        $this->app->singleton(Moonphase::class, function () use ($app) {
            return new Moonphase();
        });
    }

}
?>