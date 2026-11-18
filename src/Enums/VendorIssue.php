<?php

namespace JeffersonGoncalves\QuebecVendors\Enums;

enum VendorIssue: string
{
    case AgreementMissing = 'agreement_missing';
    case AgreementExpired = 'agreement_expired';
    case AgreementIncomplete = 'agreement_incomplete';
    case AssessmentMissing = 'assessment_missing';
    case ReviewDue = 'review_due';
    case DataReturnPending = 'data_return_pending';

    public function description(): string
    {
        return match ($this) {
            self::AgreementMissing => 'No written mandate or service contract (s. 18.3)',
            self::AgreementExpired => 'The contract has expired',
            self::AgreementIncomplete => 'The contract misses checklist items (s. 18.3)',
            self::AssessmentMissing => 'Outside Quebec without a privacy impact assessment (s. 17)',
            self::ReviewDue => 'Periodic review is due',
            self::DataReturnPending => 'Relationship ended, destruction or return of the information not confirmed',
        };
    }
}
