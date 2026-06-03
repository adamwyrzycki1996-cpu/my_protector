<?php
/**
 * MyProtector Services - Container
 * 
 * Service container implementation for dependency injection
 * 
 * @package MyProtector\Services\Container
 * @version 1.0.0
 */

namespace MyProtector\Services\Container;

/**
 * Container interface
 */
interface ContainerInterface {
    /**
     * Get a service by ID
     * 
     * @param string $id
     * @return mixed
     */
    public function get(string $id);

    /**
     * Check if service exists
     * 
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Register a service factory
     * 
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function set(string $id, callable $factory): void;

    /**
     * Register a singleton service
     * 
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function singleton(string $id, callable $factory): void;
}

/**
 * Service container implementation
 */
class ServiceContainer implements ContainerInterface {
    /**
     * Registered factories
     * 
     * @var array
     */
    protected $factories = [];

    /**
     * Singleton instances
     * 
     * @var array
     */
    protected $singletons = [];

    /**
     * Instances (non-singleton)
     * 
     * @var array
     */
    protected $instances = [];

    /**
     * Get a service
     * 
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function get(string $id) {
        if (!$this->has($id)) {
            throw new \Exception("Service '{$id}' not found in container");
        }

        // Return singleton if exists
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        // Create new instance from factory
        if (isset($this->factories[$id])) {
            $instance = ($this->factories[$id])($this);
            
            // Check if this is a singleton
            if (isset($this->singletons[$id])) {
                $this->singletons[$id] = $instance;
            }
            
            return $instance;
        }

        // Return existing instance
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        throw new \Exception("Service '{$id}' not found in container");
    }

    /**
     * Check if service exists
     * 
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool {
        return isset($this->factories[$id]) || 
               isset($this->singletons[$id]) || 
               isset($this->instances[$id]);
    }

    /**
     * Register a service factory
     * 
     * @param string $id
     * @param callable $factory
     * @return $this
     */
    public function set(string $id, callable $factory): self {
        $this->factories[$id] = $factory;
        return $this;
    }

    /**
     * Register a singleton service
     * 
     * @param string $id
     * @param callable $factory
     * @return $this
     */
    public function singleton(string $id, callable $factory): self {
        $this->factories[$id] = $factory;
        $this->singletons[$id] = null; // Mark as singleton
        return $this;
    }

    /**
     * Register an instance directly (for pre-built objects)
     * 
     * @param string $id
     * @param mixed $instance
     * @return $this
     */
    public function instance(string $id, $instance): self {
        $this->instances[$id] = $instance;
        return $this;
    }

    /**
     * Remove a service
     * 
     * @param string $id
     * @return void
     */
    public function remove(string $id): void {
        unset(
            $this->factories[$id],
            $this->singletons[$id],
            $this->instances[$id]
        );
    }

    /**
     * Clear all services
     * 
     * @return void
     */
    public function clear(): void {
        $this->factories = [];
        $this->singletons = [];
        $this->instances = [];
    }

    /**
     * Get all service IDs
     * 
     * @return array
     */
    public function keys(): array {
        return array_unique(array_merge(
            array_keys($this->factories),
            array_keys($this->singletons),
            array_keys($this->instances)
        ));
    }
}