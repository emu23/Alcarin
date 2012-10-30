<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Errors\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\ViewModel;

use Zend\InputFilter\InputFilter;

/**
 * manager external errors, probably only for debug purposes, when game will be in beta,
 * it can log players JAVASCRIPT errors from most browsers for sample.
 * it saving incoming errors in "ext_logs" and storing requests callers ips
 * in "ext_logs_limit" table, to make requests limit per day
 * ( to ExternalErrorsController::LOGS_DAY_LIMIT_PER_IP )
 */
class ExternalErrorsController extends AbstractRestfulController
{
    const LOGS_DAY_LIMIT_PER_IP = 5;

    protected $mongo = null;

    protected function mongo()
    {
        if( $this->mongo == null ) {
            $this->mongo = $this->getServiceLocator()->get('mongo');
        }
        return $this->mongo;
    }

    public function getList()
    {
        return ['getlist'=>321];
    }

    public function get( $id )
    {
        return ['get'=>321];
    }

    public function update( $id, $data )
    {
        return ['update'=>321];
    }

    public function delete( $id )
    {
        return ['delte'=>321];
    }

    private function errorInputFilter()
    {
        $inputFilter = new InputFilter();

        $filters = [
            [
                'name'     => 'line',
                'filters'  => [ ['name' => 'Int'] ],
            ],
            [
                'name'     => 'msg',
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [ [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 250,
                        ],
                ]],
            ],
            [
                'name'     => 'url',
                'allow_empty' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 100,
                        ],
                    ],
                ],
            ]
        ];

        foreach( $filters as $filter ) {
            $inputFilter->add( $filter );
        }

        return $inputFilter;
    }

    public function create( $data )
    {
        //echo json_encode($data);exit;
        $inputFilter = $this->errorInputFilter();
        $inputFilter->setData( $data );
        if( $inputFilter->isValid() ) {
            $data = $inputFilter->getValues();
            $user_ip = $this->getRequest()->getServer('REMOTE_ADDR');

            $mongo = $this->mongo();

            $limitQ = $mongo->ext_logs_limit->findOne( ['ip' => $user_ip ] );
            if( $limitQ == null ) {
                $limit = 0;
            }
            else {
                $limit = isset( $limitQ['count'] ) ? $limitQ['count'] : 0;
                if( $limit > 0 && $limitQ['last_time'] instanceof \MongoDate ) {
                    $time = $limitQ['last_time']->sec;
                    if( (time() - $time) > ( 60 * 60 * 24 ) ) {
                        //old limit, can be ommited
                        $limit = 0;
                    }
                }
            }

            if( $limit < static::LOGS_DAY_LIMIT_PER_IP ) {
                //store additional information about user
                $data['user-ip'] = $user_ip;

                $auth    = $this->getServiceLocator()->get('zfcuser_auth_service');
                if( $auth->hasIdentity() ) {
                    $user_id = new \MongoId( $auth->getIdentity()->getId() );
                    $data['user-id'] = \MongoDBRef::create( 'users', $user_id );
                }

                $mongo->ext_logs->insert( $data );
                $this->updateLogsLimit( $user_ip, $limit + 1 );
                return ['ok' => 1];
            }
        }
        return ['ok' => 0];
    }

    private function updateLogsLimit( $ip, $limit )
    {
        $this->mongo()->ext_logs_limit->update( ['ip' => $ip],
                ['ip' => $ip, 'count' => $limit, 'last_time' => new \MongoDate() ],
                ['upsert' => true] );
    }
}
