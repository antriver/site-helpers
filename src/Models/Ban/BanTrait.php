<?php

namespace Antriver\SiteUtils\Models\Ban;

use Carbon\Carbon;

trait BanTrait
{
    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= Carbon::now();
    }

    public function isPermanent(): bool
    {
        return is_null($this->expiresAt);
    }
}
