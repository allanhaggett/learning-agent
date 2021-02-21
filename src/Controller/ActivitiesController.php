<?php
declare(strict_types=1);

namespace App\Controller;

Use Cake\ORM\TableRegistry;
use Cake\ORM\Locator\LocatorAwareTrait;
// the Text class
use Cake\Utility\Text;

/**
 * Activities Controller
 *
 * @property \App\Model\Table\ActivitiesTable $Activities
 *
 * @method \App\Model\Entity\Activity[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ActivitiesController extends AppController
{
        /**
     * Export method to create an XML export method
     *
     * @return \Cake\Http\Response|null
     */
    public function export()
    {
        $this->Authorization->skipAuthorization();
        $activities = $this->Activities
                            ->find('all')
                            ->contain(['Statuses', 
                                        'Ministries', 
                                        'ActivityTypes',
                                        'Steps.Pathways'])
                            ->where(['Activities.status_id' => 2])
                            ->order(['Activities.created' => 'DESC']);

        $this->set(compact('activities'));
    }
    
    /**
     * list method 
     *
     * @return \Cake\Http\Response|null
     */
    public function list()
    {
        $this->Authorization->skipAuthorization();
        $activities = $this->Activities
                            ->find('all')
                            ->contain(['Statuses', 
                                        'Ministries', 
                                        'ActivityTypes',
                                        'Steps.Pathways'])
                            ->where(['Activities.status_id' => 2])
                            ->order(['Activities.created' => 'DESC']);
        
        $this->set(compact('activities'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();

        $allpaths = TableRegistry::getTableLocator()->get('Pathways');
        $pathways = $allpaths->find('all')->contain(['Steps','Statuses'])->order(['Pathways.created' => 'desc'])->limit(5); //->where(['status_id' => 2]);
        $allpathways = $pathways->toList();
       
        $activities = $this->Activities
                            ->find('all')
                            ->contain(['Statuses', 
                                        'Ministries', 
                                        'ActivityTypes',
                                        'Steps.Pathways'])
                            ->where(['Activities.status_id' => 2])
                            ->order(['Activities.recommended' => 'DESC'])
                            ->limit(5);
        
		$cats = TableRegistry::getTableLocator()->get('Categories');
        $allcats = $cats->find('all')->contain(['Topics'])->order(['Categories.created' => 'desc']);
        


        $this->set(compact('activities','allpathways','allcats'));
    }

    /**
     * View method
     *
     * @param string|null $id Activity id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->Authorization->skipAuthorization();
        //
        // Check to see if the current user has "claimed" 
        // this activity. Here we get the current user id and use it to select 
        // all of the claimed activities assigned to them, and then process out 
        // just the activity IDs into a simple array. Then, in the template 
        // code, we if(in_array($activity->id,$useractivitylist)):
        //
        // First let's check to see if this person is logged in or not.
        //
	    $user = $this->request->getAttribute('authentication')->getIdentity();
        if(!empty($user)) {

            // We need create am empty array first. If nothing gets added to
            // it, so be it
            $useractivitylist = array();

            // Get access to the appropriate table
            $au = TableRegistry::getTableLocator()->get('ActivitiesUsers');
            
            // Select all activities that this user has claimed
            $useacts = $au->find()->where(['user_id = ' => $user->id]);

            // convert the results into a simple array so that we can
            // use in_array in the template
            $useractivities = $useacts->toList();

            // Loop through the resources and add just the ID to the 
            // array that we will pass into the template
            // #TODO this is probably really inefficient #refactor
            foreach($useractivities as $uact) {
                array_push($useractivitylist, $uact['activity_id']);
            }


        }
        $activity = $this->Activities->get($id, [
            'contain' => ['Statuses', 
                            'Ministries', 
                            'ActivityTypes', 
                            'Users', 
                            'Competencies', 
                            'Steps', 
                            'Steps.Pathways', 
                            'Tags',
                            'Reports',
                            'Reports.Users'],
        ]);

        $allpaths = TableRegistry::getTableLocator()->get('Pathways');
        $pathways = $allpaths->find('all')->contain(['steps']);
        $allpathways = $pathways->toList();

        $this->set(compact('activity', 'useractivitylist','allpathways'));
    }







    /**
     * Find method for activities; intended for use as an auto-complete
     *  search function for adding activities to steps
     *
     * @param string|null $search search pararmeters to lookup activities.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function find()
    {
        $this->Authorization->skipAuthorization();
	    $search = $this->request->getQuery('q');
        $activities = $this->Activities->find()->contain('Steps.Pathways','ActivityTypes')->where(function ($exp, $query) use($search) {
            return $exp->like('name', '%'.$search.'%');
        })->order(['name' => 'ASC']);
        $numresults = $activities->count();
        $allpaths = TableRegistry::getTableLocator()->get('Pathways');
        $pathways = $allpaths->find('all')->contain(['steps']);
        $allpathways = $pathways->toList();
        
        $this->set(compact('activities','allpathways','search', 'numresults'));
    }

    /**
     * Time method for activities so you can show activities based on estimated_time
     *
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function estimatedtime($timeframe = null)
    {
        $this->Authorization->skipAuthorization();
        $activities = $this->Activities->find()->contain('Steps.Pathways','ActivityTypes')->where(function ($exp, $query) use($timeframe) {
            return $exp->like('estimated_time', '%'.$timeframe.'%');
        })->order(['name' => 'ASC']);
        //$activities = $this->Activities->findByEstimated_time($time)->firstOrFail();
        $numresults = $activities->count();
        
        $this->set(compact('activities','numresults','timeframe'));
    }

    /**
     * Find method for activities; intended for use as an auto-complete
     *  search function for adding activities to steps
     *
     * @param string|null $search search pararmeters to lookup activities.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function stepfind()
    {
        $this->Authorization->skipAuthorization();
        $search = $this->request->getQuery('q');
        $stepid = $this->request->getQuery('step_id');
        
        $activities = $this->Activities->find()->contain('Steps.Pathways')->where(function ($exp, $query) use($search) {
            return $exp->like('name', '%'.$search.'%');
        })->order(['name' => 'ASC']);

        $allpaths = TableRegistry::getTableLocator()->get('Pathways');
        $pathways = $allpaths->find('all')->contain(['steps']);
        $allpathways = $pathways->toList();

        $this->set(compact('activities','allpathways','stepid'));
    }





    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {

	    $user = $this->request->getAttribute('authentication')->getIdentity();
        $activity = $this->Activities->newEmptyEntity();
        $this->Authorization->authorize($activity);
        


	    if ($this->request->is('post')) {

            $activity = $this->Activities->patchEntity($activity, $this->request->getData());
            $activity->createdby_id = $user->id;
            $activity->modifiedby_id = $user->id;
            $sluggedTitle = Text::slug($this->request->getData()['name']);
            // trim slug to maximum length defined in schema
            $activity->slug = strtolower(substr($sluggedTitle, 0, 191));

            if ($this->Activities->save($activity)) {
                //print(__('The activity has been saved.'));
                $go = '/activities/view/' . $activity->id;
                return $this->redirect($go);
            }
            //print(__('The activity could not be saved. Please, try again.'));
        }
        $statuses = $this->Activities->Statuses->find('list', ['limit' => 200]);
        $ministries = $this->Activities->Ministries->find('list', ['limit' => 200]);
        $categories = $this->Activities->Categories->find('list', ['limit' => 200]);
        $activityTypes = $this->Activities->ActivityTypes->find('list', ['limit' => 200]);
        $users = $this->Activities->Users->find('list', ['limit' => 200]);
        $competencies = $this->Activities->Competencies->find('list', ['limit' => 200]);
        $steps = $this->Activities->Steps->find('list', ['limit' => 200]);
        $tags = $this->Activities->Tags->find('list', ['limit' => 200]);
        $this->set(compact('activity', 'statuses', 'ministries', 'categories', 'activityTypes', 'users', 'competencies', 'steps', 'tags'));
    }
    /**
     * Add-to-step method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function addtostep()
    {

	    $user = $this->request->getAttribute('authentication')->getIdentity();
        $activity = $this->Activities->newEmptyEntity();
        $this->Authorization->authorize($activity);

	    if ($this->request->is('post')) {

            $activity = $this->Activities->patchEntity($activity, $this->request->getData());
            $activity->createdby_id = $user->id;
            $activity->modifiedby_id = $user->id;
            $activity->status_id = 2;

            $sluggedTitle = Text::slug($activity->name);
            // trim slug to maximum length defined in schema
            $activity->slug = strtolower(substr($sluggedTitle, 0, 191));

            if ($this->Activities->save($activity)) {

                $asteptable = TableRegistry::getTableLocator()->get('ActivitiesSteps');
                $activitiesStep = $asteptable->newEmptyEntity();
                $activitiesStep->step_id = $this->request->getData()['step_id'];
                $activitiesStep->activity_id = $activity->id;
                $activitiesStep->stepcontext = $this->request->getData()['stepcontext'];

                if (!$asteptable->save($activitiesStep)) {
                    echo 'Cannot add to step! Contact an admin. Sorry :)';
                    echo '' . $activity->id;
                    exit;
                }
                return $this->redirect($this->referer());
            }
            echo __('The activity could not be saved. Please, try again.');
        }
    
    }

    /**
     * Suggest method for regular users to suggest activities
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function contribute()
    {

	    $user = $this->request->getAttribute('authentication')->getIdentity();
        $activity = $this->Activities->newEmptyEntity();
        $this->Authorization->authorize($activity);
	    if ($this->request->is('post')) {

            $activity = $this->Activities->patchEntity($activity, $this->request->getData());
            $activity->createdby_id = $user->id;
            $activity->modifiedby_id = $user->id;
            $activity->status_id = 5;
            $activity->licensing = '';
            $activity->moderator_notes = '';

            if ($this->Activities->save($activity)) {
                //print(__('The activity has been saved.'));
                $go = '/activities/view/' . $activity->id;
                return $this->redirect($go);
            }
            //print(__('The activity could not be saved. Please, try again.'));
        }
        $statuses = $this->Activities->Statuses->find('list', ['limit' => 200]);
        $ministries = $this->Activities->Ministries->find('list', ['limit' => 200]);
        $activityTypes = $this->Activities->ActivityTypes->find('list', ['limit' => 200]);
        $users = $this->Activities->Users->find('list', ['limit' => 200]);
        $competencies = $this->Activities->Competencies->find('list', ['limit' => 200]);
        $steps = $this->Activities->Steps->find('list', ['limit' => 200]);
        $tags = $this->Activities->Tags->find('list', ['limit' => 200]);
        $this->set(compact('activity', 'statuses', 'ministries', 'activityTypes', 'users', 'competencies', 'steps', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Activity id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $activity = $this->Activities->get($id, [
            'contain' => ['Users', 'Competencies', 'Steps', 'Steps.Pathways', 'Tags'],
        ]);
        $this->Authorization->authorize($activity);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $activity = $this->Activities->patchEntity($activity, $this->request->getData());
            $sluggedTitle = Text::slug($activity->name);
            // trim slug to maximum length defined in schema
            $activity->slug = strtolower(substr($sluggedTitle, 0, 191));
            
            if ($this->Activities->save($activity)) {
                //print(__('The activity has been saved.'));
                $go = '/activities/view/' . $id;
                return $this->redirect($this->referer());
            }
            //print(__('The activity could not be saved. Please, try again.'));
        }
        $statuses = $this->Activities->Statuses->find('list', ['limit' => 200]);
        $ministries = $this->Activities->Ministries->find('list', ['limit' => 200]);
        
        $activityTypes = $this->Activities->ActivityTypes->find('list', ['limit' => 200]);
        $users = $this->Activities->Users->find('list', ['limit' => 200]);
        $competencies = $this->Activities->Competencies->find('list', ['limit' => 200]);
        $steps = $this->Activities->Steps->find('list', ['limit' => 200])->contain(['pathways']);
        $tags = $this->Activities->Tags->find('list', ['limit' => 200]);
        $this->set(compact('activity', 'statuses', 'ministries', 'activityTypes', 'users', 'competencies', 'steps', 'tags'));
    }


    /**
     * Claim an activity method
     *
     * @param string|null $id Activity id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function claim($id = null)
    {
        $activity = $this->Activities->get($id, [
            'contain' => ['Users'],
        ]);
        
        $this->Authorization->authorize($activity);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $activity = $this->Activities->patchEntity($activity, $this->request->getData());
  
            if ($this->Activities->save($activity)) {
                return $this->redirect($this->referer());
            }
        }

    }

    /**
    * Like an activity
    *
    * @return \Cake\Http\Response|null Redirects to courses index.
    * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
    *
    */
    public function like ($id = null)
    {
        $activity = $this->Activities->get($id);
        $this->Authorization->authorize($activity);
        $newlike = $activity->recommended;
        $newlike++;
        $this->request->getData()['recommended'] = $newlike;
        $activity->recommended = $newlike;
        if ($this->request->is(['get'])) {
            $activity = $this->Activities->patchEntity($activity, $this->request->getData());
            if ($this->Activities->save($activity)) {
                echo 'Liked!';
                //return $this->redirect($this->referer());
            } else {
                //print(__('The activity could not be saved. Please, try again.'));
            }
        }
    }



    /**
     * Delete method
     *
     * @param string|null $id Activity id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $activity = $this->Activities->get($id);
        $this->Authorization->authorize($activity);
        if ($this->Activities->delete($activity)) {
            //print(__('The activity has been deleted.'));
        } else {
            //print(__('The activity could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }




    /**
    * Standard Activities file upload.
    * 
    * Takes a standard CSV file of activities and uploads it into the webroot/files
    * directory and redirects to activityImport
    * 
    *
    *
    * @return \Cake\Http\Response|null Redirects to activityImport for processing.
    * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found. #TODO what does it really say?
    *
    */
    public function activityImportUpload () 
    {
        $fileobject = $this->request->getData('standardimportfile');
        $destination = '/home/allankh/learningagent/webroot/files/standard-import.csv';

        // Existing files with the same name will be replaced.
        $fileobject->moveTo($destination);
        return $this->redirect(['action' => 'activityImport']);
    }

    /**
    * Standard Activities Import process.
    * 
    * Takes a standard CSV file of activities (headers below; sample file in repo)
    * and imports them into the database.
    *
    * #TODO should this be implemented at the Model/Table layer? probs...
    *
    * 0-Pathway,1-Step,2-Activity Type,3-Name,4-Hyperlink,5-Description,6-Required,
    * 7-Competencies,8-Time,9-Tags,10-Licensing,11-ISBN,12-Curator
    *
    * ___Does NOT currently support importing pathways or steps.___ It will only 
    * import activities. Curators then still need to create pathways and steps and 
    * manually associate the activities. It's on the backlog to support this, but 
    * not for MVP
    *
    * @return \Cake\Http\Response|null Redirects to courses index.
    * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
    *
    */
    public function activityImport ()
    {
        // #TODO check to see if standard-import.csv already exists,
        // make a copy of it if it does (better yet give it a unique
        // file name on upload and pass it in here)
        // #TODO Use a constant for the file path
        //
        // "Pathway","Step","2-Activity Type","Name","4-Hyperlink",
        // "Description","6-Required","Competencies","8-Time","Tags","10-Licensing","ISBN","Curator"
        //
        if (($handle = fopen("/home/allankh/learning-agent/webroot/files/standard-import.csv", "r")) !== FALSE) {
            // pop the headers off so we're starting with actual data
            fgetcsv($handle);
           
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $lic = $data[10] ?? '';
                // #TODO Should we check for existing activities before proceding? 
                $activity = $this->Activities->newEmptyEntity();
                $this->Authorization->authorize($activity);
                // Get started
                $activity->name = utf8_encode($data[3]);
                $activity->description = utf8_encode($data[5]);
                // #TODO url encode this?
                $activity->hyperlink = $data[4];

                // This is for a comment on a moderation action
                // #TODO this should probably be split out into separate
                // feature that ties into user flag reports
                // (create a moderation table for each report; add a mod_comments
                //   table for discussion of the reports)
                $activity->moderator_notes = '';

                $activity->licensing = utf8_encode($lic);

                // #TODO maybe remove? automate fetch of external metadata?
                $activity->meta_description = '';

                // Default to active (1) #TODO support adding inactive? why?
                $activity->status_id = 1;

                // #TODO do a lookup here and get the proper ID based on the person's
                // name (don't require a number here)
                $activity->modifiedby_id = utf8_encode($data[12]);
                $activity->createdby_id = utf8_encode($data[12]);
                $activity->approvedby_id =  utf8_encode($data[12]);
                
                $activity->hours = 0;
                $activity->estimated_time = utf8_encode($data[8]);
                // Is it required?
                $reqd = 0;
                if($data[6] == 'y') $reqd = 1;
                $activity->required = $reqd;

                // Competencies 
                // #TODO implement lookup and new if not exists

                // Tags
                // #TODO implement lookup and new if not exists
                // https://book.cakephp.org/4/en/tutorials-and-examples/cms/tags-and-users.html

                // 1-watch,2-read,3-listen,4-participate
                $actid = 1;
                if($data[2] == 'Watch') $actid = 1;
                if($data[2] == 'Read') $actid = 2;
                if($data[2] == 'Listen') $actid = 3;
                if($data[2] == 'Participate') $actid = 4;
                $activity->activity_types_id = $actid;
                
                if ($this->Activities->save($activity)) {
                    // do nothing, move to the next activity
                } else {
                    print_r($data);
                    echo 'Did not import ' . $data[4] . '. Something\'s wrong!<br>';
                }
            
            } // endwhile
        
            //return $this->redirect(['action' => 'index']);
        }
    }


       /**
     * Import an entire pathway from a provided JSON file
     *
     */
    public function import ($topic_id = null)
    {
        $this->Authorization->skipAuthorization();
        $user = $this->request->getAttribute('authentication')->getIdentity();

        $data = file_get_contents('/home/a/learning-curator/personal-development.json',true);
            
        $path = json_decode($data, true);
        

        foreach($path['steps'] as $step) {

            foreach($step['activities'] as $act) {

                
                // #TODO Should we check for existing activities before proceding? 
                $activity = $this->Activities->newEmptyEntity();
                
                // Get started
                $activity->name = $act['name'];
                $sluggedTitle = Text::slug($act['name']);
                $activity->slug = strtolower(substr($sluggedTitle, 0, 191));
                $activity->description = $act['description'];
                // #TODO url encode this?
                $activity->hyperlink = $act['hyperlink'];
                $activity->topic_id = $topic_id;
                $activity->status_id = 2;
                $activity->modifiedby_id = $user->id;
                $activity->createdby_id = $user->id;
                $activity->approvedby_id =  $user->id;
                $activity->estimated_time = $act['estimated_time'];
                $activity->activity_types_id = $act['activity_types_id'];

                //print_r($activity); exit;
                
                if ($this->Activities->save($activity)) {
                    // do nothing, move to the next activity
                    echo 'Created!';
                } else {
                    echo 'Did not import ' . $data[4] . '. Something\'s wrong!<br>';
                }




            }
        }




        
        

    }
}
