<?php

declare(strict_types = 1);

namespace AvtoDev\RoadRunnerLaravel\Tests;

use AvtoDev\RoadRunnerLaravel\ServiceProvider;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * @covers \AvtoDev\RoadRunnerLaravel\ServiceProvider<extended>
 */
class ServiceProviderTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testGetConfigRootKey(): void
    {
        $this->assertSame('roadrunner', ServiceProvider::getConfigRootKey());
    }

    /**
     * @return void
     */
    public function testGetConfigPath(): void
    {
        $this->assertSame(
            \realpath(__DIR__ . '/../config/roadrunner.php'),
            \realpath($path = ServiceProvider::getConfigPath())
        );

        $this->assertFileExists($path);
    }

    /**
     * @return void
     */
    public function testRegisterConfigs(): void
    {
        $package_config_src    = \realpath(ServiceProvider::getConfigPath());
        $package_config_target = $this->app->configPath(\basename(ServiceProvider::getConfigPath()));

        $this->assertSame(
            $package_config_target,
            IlluminateServiceProvider::$publishes[ServiceProvider::class][$package_config_src]
        );

        $this->assertSame(
            $package_config_target,
            IlluminateServiceProvider::$publishGroups['config'][$package_config_src],
            "Publishing group value {$package_config_target} was not found"
        );

        $rr_bin_config_src    = \realpath(__DIR__ . '/../vendor/spiral/roadrunner/.rr.yaml');
        $rr_bin_config_target = $this->app->basePath('.rr.yaml.dist');

        $this->assertSame(
            $rr_bin_config_target,
            IlluminateServiceProvider::$publishes[ServiceProvider::class][$rr_bin_config_src]
        );

        $this->assertSame(
            $rr_bin_config_target,
            IlluminateServiceProvider::$publishGroups['rr-config'][$rr_bin_config_src],
            "Publishing group value {$rr_bin_config_target} was not found"
        );
    }

    /**
     * @return void
     */
    public function testEventListenersBooting(): void
    {
        /** @var ConfigRepository $config */
        $config = $this->app->make(ConfigRepository::class);

        /** @var EventsDispatcher $events */
        $events = $this->app->make(EventsDispatcher::class);

        foreach ($config->get('roadrunner.listeners') as $event => $listeners) {
            if (! empty($listeners)) {
                $this->assertTrue($events->hasListeners($event), "Event [{$event}] has no listeners");
            }
        }
    }
}
