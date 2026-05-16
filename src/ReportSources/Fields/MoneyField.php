<?php

declare(strict_types=1);

namespace Ihasan\ReportBuilder\ReportSources\Fields;

class MoneyField extends Field
{
    protected ?string $currency = null;

    public function type(): string
    {
        return 'money';
    }

    public function currency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function currencyCode(): ?string
    {
        return $this->currency;
    }
}
