<?php declare(strict_types=1);

namespace Shopware\Core\System\StateMachine\Aggregation\StateMachineState;

use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\SearchRanking;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionDefinition;
use Shopware\Core\System\StateMachine\StateMachineDefinition;

class StateMachineStateDefinition extends EntityDefinition
{
    public static function getEntityName(): string
    {
        return 'state_machine_state';
    }

    public static function getEntityClass(): string
    {
        return StateMachineStateEntity::class;
    }

    public static function getTranslationDefinitionClass(): ?string
    {
        return StateMachineStateTranslationDefinition::class;
    }

    public static function getCollectionClass(): string
    {
        return StateMachineStateCollection::class;
    }

    protected static function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),

            (new StringField('technical_name', 'technicalName'))->setFlags(new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new TranslatedField('name'))->setFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),

            (new FkField('state_machine_id', 'stateMachineId', StateMachineDefinition::class))->setFlags(new Required()),
            new ManyToOneAssociationField('stateMachine', 'state_machine_id', StateMachineDefinition::class, false),

             new OneToManyAssociationField('fromStateMachineTransitions', StateMachineTransitionDefinition::class, 'from_state_id', false),
             new OneToManyAssociationField('toStateMachineTransitions', StateMachineTransitionDefinition::class, 'to_state_id', false),

            (new TranslationsAssociationField(StateMachineStateTranslationDefinition::class, 'state_machine_state_id'))->setFlags(new Required(), new CascadeDelete()),

            new OneToManyAssociationField('orderTransactions', OrderTransactionDefinition::class, 'state_id', false),
            new OneToManyAssociationField('orderDeliveries', OrderDeliveryDefinition::class, 'state_id', false),
            new OneToManyAssociationField('orders', OrderDefinition::class, 'state_id', false),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
