<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
*/
?>
<div class="container-fluid">
<div class="row">
<div class="col-12">
	<!-- Created on: <?= h($user->created) ?> -->
	<h1>Welcome <?= h($user->name) ?> <!-- ID: <?= h($user->id) ?> --></h1>
	<!-- <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-dark btn-sm']) ?> -->
</div>

<?php if (!empty($pathways)) : ?>
	<div class="col-4">
	<h2>Your Pathways</h2>
	<?php foreach ($pathways as $p) : ?>
	<div class="p-3 mb-3 bg-white rounded-3 shadow-sm">
	<div class="fw-light fs-4">
		<?= $p->name ?>
	</div>
	<div>Published on: <?= $p->modified ?></div>
	<div class="btn-group">
	<?= $this->Html->link(__('Publish'), ['controller' => 'Pathways', 'action' => 'make', $p->id],['class' => 'btn btn-success']) ?>
	<?= $this->Html->link(__('View'), ['controller' => 'Pathways', 'action' => 'view', $p->id],['class' => 'btn btn-light']) ?>
	<a class="btn btn-light" data-bs-toggle="collapse" href="#pathsteps<?= $p->id ?>" role="button" aria-expanded="false" aria-controls="collapseExample">Steps</a>
	</div>
	<div class="collapse" id="pathsteps<?= $p->id ?>">
	<?php foreach ($p->steps as $s) : ?>
	<?= $this->Html->link($s->name, ['controller' => 'Steps', 'action' => 'edit', $s->id]) ?> 
	<?php endforeach; ?>
	</div> <!-- /.collapse -->
	</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($categories)) : ?>
	<div class="col-4">
	<h2>Topics</h2>
	<?php foreach ($categories as $c) : ?>
	<div class="p-3 mb-3 bg-white rounded-3 shadow-sm">
	<div class="fs-4"><?= $c->name ?></div>
	<?php foreach ($c->topics as $t) : ?>
	<a href="/topics/view/<?= $t->id ?>"><?= $t->name ?></a>
	<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
	</div>
<?php endif; ?>
</div>
</div>