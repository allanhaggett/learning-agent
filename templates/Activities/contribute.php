<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Activity $activity
 */
$this->layout = 'nowrap';
$this->loadHelper('Authentication.Identity');
?>
<?= $this->Form->create($activity) ?>
<div class="container-fluid" id="colorful">
<div class="row justify-content-md-center">
<div class="col-md-6">
<div class="pad-md">
    <h1 class="mt-3">Contribute</h1>
    <h2>Suggest an activity</h2>
    </div>
</div>
</div>
</div>
<div class="container-fluid linear">
<div class="row justify-content-md-center">
<div class="col-md-4">

    <div class="card my-3">
    <div class="card-body">
    
    <?php 
    // echo $this->Form->control('ministry_id', ['class' => 'form-control', 'options' => $ministries, 'empty' => true]);
    // echo $this->Form->control('category_id', ['class' => 'form-control', 'options' => $categories, 'empty' => true]);

    ?>
    <?php echo $this->Form->control('name', ['class' => 'form-control form-control-lg']); ?>
    <?php echo $this->Form->control('description', ['class' => 'form-control']); ?>
    <?php echo $this->Form->control('hyperlink', ['class' => 'form-control']); ?>
    <?php //echo $this->Form->control('steps._ids', ['class' => 'form-control', 'options' => $steps]); ?>
    <?php //echo $this->Form->control('licensing', ['class' => 'form-control']); ?>
    <?php //echo $this->Form->control('moderator_notes', ['class' => 'form-control']); ?>
    <?php //echo $this->Form->control('isbn', ['class' => 'form-control']); ?>
    </div>
    </div>

</div>
<div class="col-md-3">
    <div class="card my-3">
    <div class="card-body">
    <?php echo $this->Form->control('activity_types_id', ['class' => 'form-control', 'options' => $activityTypes]); ?>
    <?php //echo $this->Form->control('status_id', ['class' => 'form-control', 'options' => $statuses, 'empty' => true]); ?>
    <?php echo $this->Form->control('estimated_time', ['type' => 'text', 'label' => 'Estimated Time', 'class' => 'form-control']); ?>
    <?php //echo $this->Form->control('tag_string', ['class' => 'form-control', 'type' => 'text', 'label' => 'Tags']); ?>
    <?php //echo $this->Form->control('users._ids', ['class' => 'form-control', 'options' => $users]); ?>
    <?php //echo $this->Form->control('competencies._ids', ['class' => 'form-control', 'options' => $competencies]); ?>
    <?= $this->Form->button(__('Suggest Activity'), ['class' => 'btn btn-block btn-dark my-3']) ?>
    </div>
    </div>
</div>
</div>
</div>
<?= $this->Form->end() ?>
