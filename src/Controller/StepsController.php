<?php
declare(strict_types=1);

namespace App\Controller;
Use Cake\ORM\TableRegistry;

/**
 * Steps Controller
 *
 * @property \App\Model\Table\StepsTable $Steps
 *
 * @method \App\Model\Entity\Step[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class StepsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $steps = $this->paginate($this->Steps);

        $this->set(compact('steps'));
    }

    /**
     * View method
     *
     * @param string|null $id Step id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $step = $this->Steps->get($id, [
            'contain' => ['Activities', 
                            'Activities.ActivityTypes', 
                            'Activities.Tags', 
                            'Pathways', 
                            'Pathways.Steps',
                            'Pathways.Steps.Activities',
                            'Pathways.Categories', 
                            'Pathways.Users'],
        ]);
        $this->Authorization->authorize($step);
        $user = $this->request->getAttribute('authentication')->getIdentity();

        //
        // We need a list of all the activity IDs in the entire 
        // pathway (not just this step) so that we can build the
        // activity rings.
        // 
        $pathid = $step->pathways[0]->id;
        $pathallactivities = '';
        $allstepacts = $step->pathways[0]->steps;
        $allwatch = 0;
        $allread = 0;
        $alllisten = 0;
        $allparticipate = 0;
        foreach($allstepacts as $s) {
            foreach($s->activities as $a) {
                $pathallactivities .= $a->id . '-' . $a->activity_types_id . ',';
                if($a->_joinData->required == 1) {
                    if($a->activity_types_id == 1) {
                        $allwatch++;
                    } elseif($a->activity_types_id == 2) {
                        $allread++;
                    } elseif($a->activity_types_id == 3) {
                        $alllisten++;
                    } elseif($a->activity_types_id == 4) {
                        $allparticipate++;
                    }
                }
            }
        }
        
        $this->set(compact('step','pathid','pathallactivities','allwatch','allread','alllisten','allparticipate'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $step = $this->Steps->newEmptyEntity();
        $this->Authorization->authorize($step);
        if ($this->request->is('post')) {
            $step = $this->Steps->patchEntity($step, $this->request->getData());
            if ($this->Steps->save($step)) {
                //print(__('The step has been saved.'));
                $go = '/steps/edit/' . $step->id;
                return $this->redirect($go);
            }
            //print(__('The step could not be saved. Please, try again.'));
        }
        $activities = $this->Steps->Activities->find('list', ['limit' => 200]);
        $pathways = $this->Steps->Pathways->find('list', ['limit' => 200]);
        $this->set(compact('step', 'activities', 'pathways'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Step id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $step = $this->Steps->get($id, [
            'contain' => ['Activities', 'Activities.ActivityTypes', 'Pathways'],
        ]);
        
        $this->Authorization->authorize($step);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $step = $this->Steps->patchEntity($step, $this->request->getData());
            if ($this->Steps->save($step)) {
                //print(__('The step has been saved.'));
                $pathback = '/steps/view/' . $id;
                return $this->redirect($pathback);
            }
            //print(__('The step could not be saved. Please, try again.'));
        }
        $activities = $this->Steps->Activities->find('list', ['limit' => 200]);
        
        $types = TableRegistry::getTableLocator()->get('ActivityTypes');
        $atypes = $types->find('all');

        $pathways = $this->Steps->Pathways->find('list', ['limit' => 200]);
        $this->set(compact('step', 'activities', 'pathways', 'atypes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Step id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $step = $this->Steps->get($id);
        $this->Authorization->authorize($step);
        if ($this->Steps->delete($step)) {
            //print(__('The step has been deleted.'));
        } else {
            //print(__('The step could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Status method
     *
     * @param string|null $id Step id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function status($id = null)
    {
		$this->viewBuilder()->setLayout('ajax');
        $step = $this->Steps->get($id, [
            'contain' => ['Activities'],
        ]);
        $this->Authorization->authorize($step);
        $user = $this->request->getAttribute('authentication')->getIdentity();
        // We need create an empty array first. If nothing gets added to
        // it, so be it
        $useractivitylist = array();
        // Get access to the appropriate table
        $au = TableRegistry::getTableLocator()->get('ActivitiesUsers');
        // Select based on currently logged in person
        $useacts = $au->find()->where(['user_id = ' => $user->id]);
        // convert the results into a simple array so that we can
        // use in_array in the template
        $useractivities = $useacts->toList();
        // Loop through the resources and add just the ID to the 
        // array that we will pass into the template
        foreach($useractivities as $uact) {
            array_push($useractivitylist, $uact['activity_id']);
        }
		
		$stepTime = 0;
		$defunctacts = array();
		$requiredacts = array();
		$tertiaryacts = array();
		$acts = array();

		$readstepcount = 0;
		$watchstepcount = 0;
		$listenstepcount = 0;
		$participatestepcount = 0;


		$totalacts = count($step->activities);
		$stepclaimcount = 0;

		foreach ($step->activities as $activity) {
			//print_r($activity);
			// If this is 'defunct' then we pull it out of the list 
			if($activity->status_id == 3) {
				array_push($defunctacts,$activity);
			} elseif($activity->status_id == 2) {
				// if it's required
				if($activity->_joinData->required == 1) {
                    array_push($requiredacts,$activity);
                    if($activity->activity_types_id == 1) {
                        $watchstepcount++;
                    } elseif($activity->activity_types_id == 2) {
                        $readstepcount++;
                    } elseif($activity->activity_types_id == 3) {
                        $listenstepcount++;
                    } elseif($activity->activity_types_id == 4) {
                        $participatestepcount++;
                    }
                    if(in_array($activity->id,$useractivitylist)) {
                        $stepclaimcount++;
                    }
				//Otherwise it's supplemental
				} else {
					array_push($tertiaryacts,$activity);
				}
				

				$tmp = array();
				// Loop through the whole list, add steporder to tmp array
				foreach($requiredacts as $line) {
					$tmp[] = $line->_joinData->steporder;
				}
				// Use the tmp array to sort acts list
				array_multisort($tmp, SORT_DESC, $requiredacts);
			}
		}

		$stepacts = count($requiredacts);
		$completeclass = 'notcompleted'; 
		if($stepclaimcount == $totalacts) {
			$completeclass = 'completed';
		}

		if($stepclaimcount > 0) {
			$steppercent = ceil(($stepclaimcount * 100) / $stepacts);
		} else {
			$steppercent = 0;
		}		
		

        $this->set(compact('step','steppercent','stepacts'));
    }
	
}
