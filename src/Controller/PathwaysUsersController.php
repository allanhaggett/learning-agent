<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * PathwaysUsers Controller
 *
 * @property \App\Model\Table\PathwaysUsersTable $PathwaysUsers
 * @method \App\Model\Entity\PathwaysUser[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PathwaysUsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users', 'Pathways', 'Statuses'],
        ];
        $pathwaysUsers = $this->paginate($this->PathwaysUsers);

        $this->set(compact('pathwaysUsers'));
    }

    /**
     * View method
     *
     * @param string|null $id Pathways User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $pathwaysUser = $this->PathwaysUsers->get($id, [
            'contain' => ['Users', 'Pathways', 'Statuses'],
        ]);

        $this->set(compact('pathwaysUser'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $pathwaysUser = $this->PathwaysUsers->newEmptyEntity();
        if ($this->request->is('post')) {
            $pathwaysUser = $this->PathwaysUsers->patchEntity($pathwaysUser, $this->request->getData());
            if ($this->PathwaysUsers->save($pathwaysUser)) {
                $this->Flash->success(__('The pathways user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The pathways user could not be saved. Please, try again.'));
        }
        $users = $this->PathwaysUsers->Users->find('list', ['limit' => 200]);
        $pathways = $this->PathwaysUsers->Pathways->find('list', ['limit' => 200]);
        $statuses = $this->PathwaysUsers->Statuses->find('list', ['limit' => 200]);
        $this->set(compact('pathwaysUser', 'users', 'pathways', 'statuses'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Pathways User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $pathwaysUser = $this->PathwaysUsers->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $pathwaysUser = $this->PathwaysUsers->patchEntity($pathwaysUser, $this->request->getData());
            if ($this->PathwaysUsers->save($pathwaysUser)) {
                $this->Flash->success(__('The pathways user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The pathways user could not be saved. Please, try again.'));
        }
        $users = $this->PathwaysUsers->Users->find('list', ['limit' => 200]);
        $pathways = $this->PathwaysUsers->Pathways->find('list', ['limit' => 200]);
        $statuses = $this->PathwaysUsers->Statuses->find('list', ['limit' => 200]);
        $this->set(compact('pathwaysUser', 'users', 'pathways', 'statuses'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Pathways User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $pathwaysUser = $this->PathwaysUsers->get($id);
        if ($this->PathwaysUsers->delete($pathwaysUser)) {
            $this->Flash->success(__('The pathways user has been deleted.'));
        } else {
            $this->Flash->error(__('The pathways user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
