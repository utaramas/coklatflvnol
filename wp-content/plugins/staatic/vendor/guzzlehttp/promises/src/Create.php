<?php

namespace Staatic\Vendor\GuzzleHttp\Promise;

final class Create
{
    public static function promiseFor($value)
    {
        if ($value instanceof PromiseInterface) {
            return $value;
        }
        if (\is_object($value) && \method_exists($value, 'then')) {
            $wfn = \method_exists($value, 'wait') ? [$value, 'wait'] : null;
            $cfn = \method_exists($value, 'cancel') ? [$value, 'cancel'] : null;
            $promise = new Promise($wfn, $cfn);
            $value->then([$promise, 'resolve'], [$promise, 'reject']);
            return $promise;
        }
        return new FulfilledPromise($value);
    }
    public static function rejectionFor($reason)
    {
        if ($reason instanceof PromiseInterface) {
            return $reason;
        }
        return new RejectedPromise($reason);
    }
    public static function exceptionFor($reason)
    {
        if ($reason instanceof \Exception || $reason instanceof \Throwable) {
            return $reason;
        }
        return new RejectionException($reason);
    }
    public static function iterFor($value)
    {
        if ($value instanceof \Iterator) {
            return $value;
        }
        if (\is_array($value)) {
            return new \ArrayIterator($value);
        }
        return new \ArrayIterator([$value]);
    }
}
