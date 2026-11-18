<?php

namespace JeffersonGoncalves\QuebecVendors\Enums;

enum VendorRole: string
{
    /**
     * Receives personal information to carry out a mandate or a service contract for you, without the
     * person's consent. Needs a written contract (s. 18.3).
     */
    case ServiceProvider = 'service_provider';

    /** Receives personal information for its own purposes: needs the person's consent, no mandate contract. */
    case ThirdParty = 'third_party';

    public function needsAgreement(): bool
    {
        return $this === self::ServiceProvider;
    }
}
