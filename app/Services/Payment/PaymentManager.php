<?php

namespace App\Services\Payment;

use App\Contracts\PaymentServiceInterface;
use InvalidArgumentException;

class PaymentManager
{
    protected array $drivers = [];

    protected string $defaultDriver;

    public function __construct()
    {
        $this->defaultDriver = config('payment.default_driver', 'mock');

        // Register available payment drivers
        $this->drivers = [
            'mock' => MockPaymentService::class,
            'stripe' => StripePaymentService::class,
            // 'paypal' => PayPalPaymentService::class,
            // 'square' => SquarePaymentService::class,
        ];
    }

    /**
     * Get a payment service driver
     */
    public function driver(?string $driver = null): PaymentServiceInterface
    {
        $driver = $driver ?? $this->defaultDriver;

        if (! isset($this->drivers[$driver])) {
            throw new InvalidArgumentException("Payment driver [{$driver}] not found.");
        }

        $driverClass = $this->drivers[$driver];
        $instance = app($driverClass);

        if (! $instance instanceof PaymentServiceInterface) {
            throw new InvalidArgumentException("Payment driver [{$driver}] must implement PaymentServiceInterface.");
        }

        return $instance;
    }

    /**
     * Get all available payment drivers
     */
    public function getAvailableDrivers(): array
    {
        $available = [];

        foreach ($this->drivers as $name => $class) {
            $driver = app($class);
            if ($driver instanceof PaymentServiceInterface && $driver->isAvailable()) {
                $available[$name] = $driver->getName();
            }
        }

        return $available;
    }

    /**
     * Get the default driver name
     */
    public function getDefaultDriver(): string
    {
        return $this->defaultDriver;
    }

    /**
     * Set the default driver
     */
    public function setDefaultDriver(string $driver): void
    {
        $this->defaultDriver = $driver;
    }

    /**
     * Dynamically call the default driver instance
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
