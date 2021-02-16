<?php

namespace App\Http\Middleware;

use Closure;

use IPv4;

class IpMiddleware
{
    protected $subnets;
    
    public function __construct()
    {
        $this->subnets = [
            new IPv4\SubnetCalculator('192.168.0.0', 22),
            new IPv4\SubnetCalculator('10.38.128.0', 24),
            new IPv4\SubnetCalculator('10.1.0.0', 24),
            new IPv4\SubnetCalculator('10.1.1.0', 24),
            new IPv4\SubnetCalculator('10.1.2.0', 24),
            new IPv4\SubnetCalculator('127.0.0.0', 8),
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->inSubnets($request->ip())) {
            return $next($request);
        }

        return redirect(503);
    }

    protected function inSubnets($ip)
    {
        foreach ($this->subnets as $subnet) {
            if ($subnet->isIPAddressInSubnet($ip)) {
                return true;
            }
        }

        return false;
    }
}
