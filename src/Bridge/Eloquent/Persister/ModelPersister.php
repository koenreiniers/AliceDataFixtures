<?php

/*
 * This file is part of the Fidry\AliceDataFixtures package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\AliceDataFixtures\Bridge\Eloquent\Persister;

use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Nelmio\Alice\NotClonableTrait;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class ModelPersister implements PersisterInterface
{
    use NotClonableTrait;

    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @var Model[]
     */
    private $persistedModels = [];

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @inheritdoc
     */
    public function persist($object)
    {
        if (false === $object instanceof Model) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected object to be an instance of "%s", got "%s" instead.',
                    Model::class,
                    get_class($object)
                )
            );
        }

        $this->persistedModels[] = $object;
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $models = $this->persistedModels;
        $this->databaseManager->connection()->transaction(
            function () use ($models) {
                foreach ($models as $model) {
                    $model->push();
                }
            }
        );

        $this->persistedModels = [];
    }
}