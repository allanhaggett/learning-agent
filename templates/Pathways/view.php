<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Pathway $pathway
 */
$this->layout = 'nowrap';
$this->loadHelper('Authentication.Identity');
$uid = 0;
$role = 0;
if ($this->Identity->isLoggedIn()) {
	$role = $this->Identity->get('role_id');
	$uid = $this->Identity->get('id');
}
$totalusers = count($usersonthispathway);
$this->assign('title', h($pathway->name));
$activitylist = '';
foreach ($pathway->steps as $steps) {

	$stepTime = 0;
	$defunctacts = array();
	$requiredacts = array();
	$supplementalacts = array();
	$acts = array();
	
	$readstepcount = 0;
	$watchstepcount = 0;
	$listenstepcount = 0;
	$participatestepcount = 0;
	$readcolor = '';
	$watchcolor = '';
	$listencolor = '';
	$participatecolor = '';
	
	$stepclaimcount = 0;
	
	foreach ($steps->activities as $activity) {
		//print_r($activity);
		// If this is 'defunct' then we pull it out of the list 
		if($activity->status_id == 3) {
			array_push($defunctacts,$activity);
		} elseif($activity->status_id == 2) {
			
			array_push($acts,$activity);
			// if it's required
			if($activity->_joinData->required == 1) {
				array_push($requiredacts,$activity);
	
			// Otherwise it's supplemental
			} else {
				array_push($supplementalacts,$activity);
			}
			
			$activitylist .= $activity->id . ',';
			
		}
	}
	$totalacts = count($steps->activities);

}
?>


<style>
/* Start desktop-specific code for this page.
Arbitrarily set to 45em based on sample code from ...somewhere. 
This seems to work out, but #TODO investigate optimizing this
*/
@media (min-width: 45em) {
	/* probably can be removed as probably not using columns any more */
    .card-columns {
        -webkit-column-count:2;
        -moz-column-count:2;
        column-count:2;
    }
	
	.stickyrings {
		align-self: flex-start; 
		position: -webkit-sticky;
		position: sticky;
		
		top: 86px;
		z-index: 1000;
	}
} /* end desktop-specific code */

.following {
	font-size: 20px;
	font-weight: 200;
	text-align: center;
}
.stickynav {
	align-self: flex-start; 
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 1000;
}
#stepnav {
	box-shadow: 0 0 20px rgba(0,0,0,.05);
}
.nav-pills .nav-link.active, .nav-pills .show > .nav-link {
	background-color: #F1F1F1;
	color: #333;
}
</style>



<div class="container-fluid">
<div class="row justify-content-md-center" id="colorful">
<div class="col-md-6">
<div class="p-3">
	<?php if($pathway->status_id == 1): ?>
	<span class="badge badge-warning" title="Edit to set to publish">DRAFT</span>
	<?php endif ?>

	<nav aria-label="breadcrumb">
	<ol class="breadcrumb mt-3">
		<li class="breadcrumb-item"><?= $pathway->has('category') ? $this->Html->link($pathway->category->name, ['controller' => 'Categories', 'action' => 'view', $pathway->category->id]) : '' ?></li>
		<li class="breadcrumb-item" aria-current="page"><?= h($pathway->name) ?> </li>
	</ol>
	</nav> 

	<?php if($role == 2 || $role == 5): ?>
	<div class="btn-group float-right">
	<?= $this->Html->link(__('Edit'), ['action' => 'edit', $pathway->id], ['class' => 'btn btn-light']) ?>
	<?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $pathway->id], ['confirm' => __('Are you sure you want to delete # {0}?', $pathway->id), 'class' => 'btn btn-light']) ?>
	</div>
	<?php endif ?>
	<h1><?= h($pathway->name) ?></h1>

	<div class="py-3" style="background-color: rgba(255,255,255,.5)">
	<?= $pathway->objective ?> 
	
	<div class="my-2"><em>Estimated time for this pathway: <?= h($pathway->estimated_time) ?></em></div>

	<div class="mb-2">
		<span class="badge badge-light readtotal"></span>  
		<span class="badge badge-light watchtotal"></span>  
		<span class="badge badge-light listentotal"></span>  
		<span class="badge badge-light participatetotal"></span>  
	</div>
	
	<?php if($role == 2 || $role == 5): ?>

	<a class="" 
		data-toggle="collapse" 
		href="#addstepform" 
		role="button" 
		aria-expanded="false" 
		aria-controls="addstepform">
			<span>+</span> Add a step
	</a>
	<div class="collapse" id="addstepform">
	<div class="card my-3">
	<div class="card-body">
	<?= $this->Form->create(null, ['url' => [
			'controller' => 'Steps',
			'action' => 'add'
	]]) ?>
	<fieldset>
	<legend class=""><?= __('Add Step') ?></legend>
	<?php
	echo $this->Form->control('name',['class'=>'form-control']);
	echo $this->Form->control('description',['class' => 'form-control', 'type' => 'textarea']);
	echo $this->Form->hidden('createdby', ['value' => $uid]);
	echo $this->Form->hidden('modifiedby', ['value' => $uid]);
	echo $this->Form->hidden('pathways.1.id', ['value' => $pathway->id]);
	?>
	</fieldset>
	<?= $this->Form->button(__('Add Step'), ['class'=>'btn btn-block btn-primary']) ?>
	<?= $this->Form->end() ?>
	</div>
	</div>
	</div>

	<?php endif ?>
</div>
</div>
</div>
</div>
</div>
<div class="container-fluid linear">
<div class="row justify-content-md-center">

<div class="col-6 col-md-3 col-lg-2">


<div class="py-3 stickyrings">

<div id="paths" style="display: none" >
	<a href="#" class="btn btn-dark btn-block btn-lg" id="followme" onclick="return followit()">Follow</a>
	<div class="bg-light rounded-lg p-3 mt-3">
		Following a pathway is a commitment to moving 
		through each step and claiming each required activity as you complete it.
		Fill your activity rings and get a certificate!
	</div>
</div>



<!--When you select to follow a pathway, this pathway will show as a journey you are on and may be 
accessed from your profile page. Think of it as “bookmarking” learning you want to come back to and track your progress on.-->


<!--<div class="" style="font-size: 12px">Published <?= h(date('D M jS \'y',strtotime($pathway->created))) ?></div>-->

</div>
</div>
<?php if (!empty($pathway->steps)) : ?>

<div class="col-md-6 col-lg-4">

<?php foreach($pathway->steps as $steps): ?>
<?php
$readstepcount = 0;
$watchstepcount = 0;
$listenstepcount = 0;
$participatestepcount = 0;
$readcolor = '';
$watchcolor = '';
$listencolor = '';
$participatecolor = '';
foreach ($steps->activities as $activity) {
	if($activity->activity_types_id == 1) {
		$watchstepcount++;
		$watchcolor = $activity->activity_type->color;
	} elseif($activity->activity_types_id == 2) {
		$readstepcount++;
		$readcolor = $activity->activity_type->color;
	} elseif($activity->activity_types_id == 3) {
		$listenstepcount++;
		$listencolor = $activity->activity_type->color;
	} elseif($activity->activity_types_id == 4) {
		$participatestepcount++;
		$participatecolor = $activity->activity_type->color;
	}

}
?>
<div class="p-3 my-3 bg-white rounded-lg">
	<h2>

		<a href="/learning-curator/pathways/<?= $pathway->slug ?>/step/<?= $steps->id ?>">
			<?= h($steps->name) ?> 
			<i class="fas fa-arrow-circle-right"></i>
		</a>
	</h2>
	<div style="font-size; 130%"><?= $steps->description ?></div>

	<div class="my-3">
			<!-- <span class="badge rounded-pill bg-light text-dark"><?= $totalacts ?> total activities</span> 
			<span class="badge rounded-pill bg-light text-dark"><?= $stepacts ?> required</span>
			<span class="badge rounded-pill bg-light text-dark"><?= $supplmentalcount ?> supplemental</span> -->
			<span class="badge rounded-pill bg-light text-dark" style="background-color: rgba(<?= $readcolor ?>,1) !important">
				<?= $readstepcount ?> to read
			</span>  
			<span class="badge rounded-pill bg-light text-dark" style="background-color: rgba(<?= $watchcolor ?>,1) !important">
				<?= $watchstepcount ?> to watch
			</span>  
			<span class="badge rounded-pill bg-light text-dark" style="background-color: rgba(<?= $listencolor ?>,1) !important">
				<?= $listenstepcount ?> to listen to
			</span>  
			<span class="badge rounded-pill bg-light text-dark" style="background-color: rgba(<?= $participatecolor ?>,1) !important">
				<?= $participatestepcount ?> to participate in
			</span>  
		</div>

</div>
<?php endforeach ?>


</div> <!-- /.col-md -->
<?php else: ?>
<div>There don't appear to be any steps assigned to this pathway yet.</div>
<?php endif; // are there any steps at all? ?>

</div>

</div>


<script src="//cdn.jsdelivr.net/npm/pouchdb@7.2.1/dist/pouchdb.min.js"></script>
<script>

	var pathwayid = <?= $pathway->id ?>;
	var activitylist = '<?= $activitylist ?>';
	// The PHP generated the comma-separated list
	// now split into an array
	var acts = activitylist.split(',');

	var db = new PouchDB('curator-ta'); // http://localhost:5984/
	var count = 0;

	db.allDocs({include_docs: true, descending: true}, function(err, doc) {
		//
		// Pathways
		// Compare the ID provided in the markup to the 
		// ID in the localstore. If we're following this
		// pathway, then update the UI to say so, otherwise
		// we just show the default follow button that's 
		// already in the markup
		//
		doc.rows.forEach(function(e,index){
			if(e.doc['pathway'] == pathwayid) {
				document.getElementById("paths").innerHTML = '<h1>Following!</h1>';
			}


			//
			// Activities
			//
			acts.forEach(function(item, index, arr) {
				if(e.doc['activity'] == item) {
					count++;
				}
			});
			
		});
		console.log(count);
		document.getElementById("paths").style.display = 'block';
		
	});

	function followit () {		
		rightnow = new Date().getTime();
		var doc = {
			"_id": rightnow.toString(),
			"date": rightnow.toString(),
			"pathway": pathwayid,
		};
		db.put(doc);
		document.getElementById("paths").innerHTML = '<h1>Following!</h1>';
		return false;
	};



//Creating remote database object
var remoteDB = new PouchDB('http://localhost:5984/curator-ta');
//Synchronising Remote and local databases
db.sync(remoteDB, function(err, response) {
   if (err) {
      return console.log(err);
   } else {
      console.log('huzzah');
   }
});

</script>