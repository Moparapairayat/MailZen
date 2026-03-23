<?php

namespace App\Services\Tracking;

use App\Models\TrackingDomain;
use Illuminate\Http\Request;

class TrackingDomainResolver
{
    public function resolve(Request $request): TrackingDomain
    {
        $host = $request->getHost();

        $trackingDomain = TrackingDomain::where('domain', $host)
            ->where('status', 'verified')
            ->first();

        if (!$trackingDomain || !$trackingDomain->isVerified()) {
            abort(404);
        }

        // Attach to request for downstream usage
        $request->attributes->set('tracking_domain', $trackingDomain);

        return $trackingDomain;
    }
}


