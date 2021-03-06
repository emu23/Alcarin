================
AJAX_ programming
================

On alcarin programming process you will often need AJAX_ methods - because
one of our objectives is creating rich user interface. Instead of default jquery ajax
methods you should use one of two alcarin ajax managing ways. First is RESTful style
requests, second - event style requests. Second way is prefered when you create ajax-only
controllers (what you should do in most cases).

.. _AJAX: http://pl.wikipedia.org/wiki/AJAX

Urls
====

Transfering ajax targets urls by html *data* attributes and others html ways is trivial,
awkward and time-consuming. To make it easier we just simple put static urls to global
accesible **urls** object. You will find it in *..Alcarin\\static\\coffee\\urls.coffee*.
When you need new urls for your ajax calls, just add it to this object and use anywhere
in code.

RESTful_ requests
================

To use RESTful_ requests style first you prepare default `Zend Restful Controller`_. To make
work easier, you should consider using *Core\\Controller\\AbstractAlcarinRestfulController*
instead of default *AbstractRestfulController*. It implement all 4 defaults restful methods,
so you not must implement them all (if you need only part of it). Moreover it give you some
better errors support (in JSON mode) and two shortcuts method:
    * mongo() - returing mongodb object, our wrapper for default Mongo php drivers
    * gameServices() -returning GameServiceContainer instance, use with our code subsystem: :ref:`plugable-game-services`.

To work faster with JSON responses, you should use alcarin **"json()"** controller plugin.
Here is few samples of use it:

.. code-block:: php

    <?php

    ...
    public function getList()
    {
        //this same like:
        //return $this->json()->__invoke(...);
        //it will return jsonmodel
        return $this->json(['text' => 'this is page information']);
    }

    public function get($id)
    {
        if($id > 0) {
            return $this->json()->success(['text' => 'It working nice']);
        }
        else {
            return $this->json()->fail(['text' => 'It working bad']);
        }
    }

Methods *success()* and *fail()* will generate response array like this:
    ['success' => true/false]
And merge it with array in their first argument(if any). It is generally
good practice to add *'success'* flag to any requests response and check
it in coffee code.

On client side you can use default jquery ajax methods, but simplest will be use
alcaring **Rest()** wrapper. It is lazy created instance of
*Alcarin.JQueryPlugins.RESTful* class. It has 4 interesting methods:
    1. *$get* - calling simple RESTful get/getList method (according from url argument)
    2. *$create (alias $post)* - calling RESTful create method
    3. *$update (alias $put)* - calling RESTful update method
    4. *$delete* - calling RESTful delete method

Any of this methods have 3 arguments:
    1. *url* - url to call
    2. *data* - data to send with requests
    3. *on_done* - onsuccess/onfail response

You can skip data argument and use just url and on_done, for sample

.. code-block:: javascript
    Rest().$get urls.test_page, (response)=>
        alert response

.. _RESTful: http://en.wikipedia.org/wiki/Representational_state_transfer
.. _`Zend Restful Controller`: http://framework.zend.com/manual/2.1/en/modules/zend.mvc.controllers.html#the-abstractrestfulcontroller

Event style requests
====================

To make working with ajax more comfortable we implemented event style requests system. It is
trying to imitate working with true sockets system on non-web programming. It interface is
so simple, so we can later add some web sockets system.

First, on client side, you should prepare **Alcarin.EventProxy** class instance.

.. code-block:: javascript

    proxy = new Alcarin.EventProxy urls.test_page

Now you can use one of event proxy functions:
    * *proxy.emit(event_name, event_data)* - sending event to server
    * *proxy.on(response_event_name, response_callback)* - register method that will be called when server send specific event to client
    * *proxy.off(response_event_name, response_callback) - unregister method registered by *on()*

So, you can just simple register all possible responses once and then emit event to server
when you need it to do something. Here you can see few samples:

.. code-block:: javascript

    proxy.on 'test.all', (response)->
        console.log 'server send this list to us:'
        console.log response.list
        proxy.emit 'test.delete', {id: 15}

    proxy.on 'test.error', (response)->
        alert response.error

    proxy.emit 'test.fetch'


It is designed to work together with *Core\\Controller\\AbstractEventController*. So, let
create simple controller that will respond for our test code.

.. code-block:: php

    <?php

    ...

    use Core\Controller\AbstractEventController;

    class TestController extends AbstractEventController
    {
        protected function on($event_name, $data)
        {
            switch($event_name) {
                case 'test.fetch':
                    $list = [1, 2, 3];
                    $response = $this->success(['list' => $list]);
                    return $this->emit('test.all', $response);
                case 'test.delete':
                    $id = $data['id'];
                    if($id == 15) {
                        $response = $this->fail(['error' => 'this is sample error.']);
                    }
                    else {
                        $response = $this->success();
                    }
                    return $this->emit('test.error', $response);
                default:
                    return parent::on($event_name, $data);
            }
        }
    }

So, you can simple response for all events in controller *on()* method. But to make life
more easier, you can use automatic AbstractEventController event mapping system. It will
use event name to generate method by this scheme:
    * 'test.error' - onTestError()
    * 'my.long.event' - onMyLongEvent()

Now your controller can looks like that:

.. code-block:: php

    <?php

    ...

    use Core\Controller\AbstractEventController;

    class TestController extends AbstractEventController
    {
        protected function onTestFetch($data)
        {
            $list = [1, 2, 3];
            $response = $this->success(['list' => $list]);
            return $this->emit('test.all', $response);
        }

        protected function onTestDelete($data)
        {
            $id = $data['id'];
            if($id == 15) {
                $response = $this->fail(['error' => 'this is sample error.']);
            }
            else {
                $response = $this->success();
            }
            return $this->emit('test.error', $response);
        }
    }

Now it looks much clearer, is not it?