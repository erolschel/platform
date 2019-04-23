<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Delivery;

use Shopware\Core\Checkout\Cart\Delivery\Struct\Delivery;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use Shopware\Core\Checkout\Shipping\Aggregate\ShippingMethodPrice\ShippingMethodPriceEntity;
use Shopware\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DeliveryCalculator
{
    public const CALCULATION_BY_LINE_ITEM_COUNT = 1;

    public const CALCULATION_BY_PRICE = 2;

    public const CALCULATION_BY_WEIGHT = 3;

    /**
     * @var QuantityPriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    public function __construct(
        QuantityPriceCalculator $priceCalculator,
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
    }

    public function calculate(DeliveryCollection $deliveries, SalesChannelContext $context): void
    {
        foreach ($deliveries as $delivery) {
            $this->calculateDelivery($delivery, $context);
        }
    }

    private function calculateDelivery(Delivery $delivery, SalesChannelContext $context): void
    {
        $costs = null;
        if ($delivery->getShippingCosts()->getUnitPrice() > 0) {
            $costs = $this->calculateShippingCosts(
                $delivery->getShippingCosts()->getTotalPrice(),
                $delivery->getPositions()->getLineItems(),
                $context
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        if ($this->hasDeliveryShippingFreeItems($delivery)) {
            $costs = $this->calculateShippingCosts(
                0,
                $delivery->getPositions()->getLineItems(),
                $context
            );

            $delivery->setShippingCosts($costs);

            return;
        }

        $shippingMethod = $delivery->getShippingMethod();
        $prices = $shippingMethod->getPrices();
        $prices->sort(
            function (ShippingMethodPriceEntity $priceEntityA, ShippingMethodPriceEntity $priceEntityB) {
                return $priceEntityA->getPrice() <=> $priceEntityB->getPrice();
            }
        );

        foreach ($prices as $price) {
            if (!$this->matches($delivery, $price, $context)) {
                continue;
            }

            $costs = $this->calculateShippingCosts(
                $price->getPrice(),
                $delivery->getPositions()->getLineItems(),
                $context
            );
            break;
        }

        if (!$costs) {
            $delivery->setError(new ShippingMethodBlockedError($shippingMethod->getName() ?? $shippingMethod->getId()));

            return;
        }

        $delivery->setShippingCosts($costs);
    }

    private function hasDeliveryShippingFreeItems(Delivery $delivery): bool
    {
        $freeDeliveryItems = $delivery->getPositions()->getLineItems()->filter(function (LineItem $lineItem) {
            return !$lineItem->getDeliveryInformation() ? false : $lineItem->getDeliveryInformation()->getFreeDelivery();
        });

        return (bool) $freeDeliveryItems->count();
    }

    private function matches(Delivery $delivery, ShippingMethodPriceEntity $shippingMethodPrice, SalesChannelContext $context): bool
    {
        if ($shippingMethodPrice->getCalculationRuleId()) {
            return in_array($shippingMethodPrice->getCalculationRuleId(), $context->getRuleIds(), true);
        }

        $start = $shippingMethodPrice->getQuantityStart();
        $end = $shippingMethodPrice->getQuantityEnd();

        switch ($shippingMethodPrice->getCalculation()) {
            case self::CALCULATION_BY_PRICE:
                $value = $delivery->getPositions()->getPrices()->sum()->getTotalPrice();
                break;
            case self::CALCULATION_BY_LINE_ITEM_COUNT:
                $value = $delivery->getPositions()->getQuantity();
                break;
            case self::CALCULATION_BY_WEIGHT:
                $value = $delivery->getPositions()->getWeight();
                break;
            default:
                $value = $delivery->getPositions()->getLineItems()->getPrices()->sum()->getTotalPrice() / 100;
                break;
        }

        // $end (optional) exclusive
        return ($value >= $start) && (!$end || $value < $end);
    }

    private function calculateShippingCosts(float $price, LineItemCollection $calculatedLineItems, SalesChannelContext $context): CalculatedPrice
    {
        $rules = $this->percentageTaxRuleBuilder->buildRules(
            $calculatedLineItems->getPrices()->sum()
        );

        $definition = new QuantityPriceDefinition($price, $rules, $context->getContext()->getCurrencyPrecision(), 1, true);

        return $this->priceCalculator->calculate($definition, $context);
    }
}
