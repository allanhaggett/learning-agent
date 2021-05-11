<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ActivitiesTag[]|\Cake\Collection\CollectionInterface $activitiesTags
 */
?>
<div class="activitiesTags index content">
    <?= $this->Html->link(__('New Activities Tag'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Activities Tags') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('activity_id') ?></th>
                    <th><?= $this->Paginator->sort('tag_id') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activitiesTags as $activitiesTag): ?>
                <tr>
                    <td><?= $this->Number->format($activitiesTag->id) ?></td>
                    <td><?= $activitiesTag->has('activity') ? $this->Html->link($activitiesTag->activity->name, ['controller' => 'Activities', 'action' => 'view', $activitiesTag->activity->id]) : '' ?></td>
                    <td><?= $activitiesTag->has('tag') ? $this->Html->link($activitiesTag->tag->name, ['controller' => 'Tags', 'action' => 'view', $activitiesTag->tag->id]) : '' ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $activitiesTag->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $activitiesTag->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $activitiesTag->id], ['confirm' => __('Are you sure you want to delete # {0}?', $activitiesTag->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
