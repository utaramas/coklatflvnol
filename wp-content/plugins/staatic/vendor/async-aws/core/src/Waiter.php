<?php

declare (strict_types=1);
namespace Staatic\Vendor\AsyncAws\Core;

use Staatic\Vendor\AsyncAws\Core\Exception\Http\HttpException;
use Staatic\Vendor\AsyncAws\Core\Exception\Http\NetworkException;
use Staatic\Vendor\AsyncAws\Core\Exception\LogicException;
class Waiter
{
    const STATE_SUCCESS = 'success';
    const STATE_FAILURE = 'failure';
    const STATE_PENDING = 'pending';
    const WAIT_TIMEOUT = 30.0;
    const WAIT_DELAY = 5.0;
    protected $awsClient;
    protected $input;
    private $response;
    private $needRefresh = \false;
    private $finalState;
    private $resolved = \false;
    public function __construct(Response $response, AbstractApi $awsClient, $request)
    {
        $this->response = $response;
        $this->awsClient = $awsClient;
        $this->input = $request;
    }
    public function __destruct()
    {
        if (!$this->resolved) {
            $this->resolve();
        }
    }
    public final function isSuccess() : bool
    {
        return self::STATE_SUCCESS === $this->getState();
    }
    public final function isFailure() : bool
    {
        return self::STATE_FAILURE === $this->getState();
    }
    public final function isPending() : bool
    {
        return self::STATE_PENDING === $this->getState();
    }
    public final function getState() : string
    {
        if (null !== $this->finalState) {
            return $this->finalState;
        }
        if ($this->needRefresh) {
            $this->stealResponse($this->refreshState());
        }
        try {
            $this->response->resolve();
            $exception = null;
        } catch (HttpException $exception) {
        } finally {
            $this->resolved = \true;
            $this->needRefresh = \true;
        }
        $state = $this->extractState($this->response, $exception);
        switch ($state) {
            case self::STATE_SUCCESS:
            case self::STATE_FAILURE:
                $this->finalState = $state;
                break;
            case self::STATE_PENDING:
                break;
            default:
                throw new LogicException(\sprintf('Unexpected state "%s" from Waiter "%s".', $state, __CLASS__));
        }
        return $state;
    }
    /**
     * @param float|null $timeout
     */
    public final function resolve($timeout = null) : bool
    {
        try {
            return $this->response->resolve($timeout);
        } catch (HttpException $exception) {
            return \true;
        } finally {
            $this->resolved = \true;
        }
    }
    public final function info() : array
    {
        return $this->response->info();
    }
    /**
     * @return void
     */
    public final function cancel()
    {
        $this->response->cancel();
        $this->needRefresh = \true;
        $this->resolved = \true;
    }
    /**
     * @param float|null $timeout
     * @param float|null $delay
     */
    public final function wait($timeout = null, $delay = null) : bool
    {
        if (null !== $this->finalState) {
            return \true;
        }
        $timeout = $timeout ?? static::WAIT_TIMEOUT;
        $delay = $delay ?? static::WAIT_DELAY;
        $start = \microtime(\true);
        while (\true) {
            if ($this->needRefresh) {
                $this->stealResponse($this->refreshState());
            }
            if (!$this->resolve($timeout - (\microtime(\true) - $start))) {
                break;
            }
            $this->getState();
            if ($this->finalState) {
                return \true;
            }
            if ($delay > $timeout - (\microtime(\true) - $start)) {
                break;
            }
            \usleep((int) \ceil($delay * 1000000));
        }
        return \false;
    }
    /**
     * @param Response $response
     * @param HttpException|null $exception
     */
    protected function extractState($response, $exception) : string
    {
        return self::STATE_PENDING;
    }
    protected function refreshState() : Waiter
    {
        return $this;
    }
    /**
     * @return void
     */
    private function stealResponse(self $waiter)
    {
        $this->response = $waiter->response;
        $this->resolved = $waiter->resolved;
        $waiter->resolved = \true;
        $this->needRefresh = \false;
    }
}
