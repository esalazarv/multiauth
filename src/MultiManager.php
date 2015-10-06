<?php namespace Ollieread\Multiauth;

use Illuminate\Foundation\Application;

/**
 * Class MultiManager
 * @package Ollieread\Multiauth
 */
class MultiManager
{

    /**
     * Registered multiauth providers.
     *
     * @var array
     */
    protected $providers = array();
    protected $currentProvider = null;
    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        foreach ($app['config']['auth.multi'] as $key => $config) {
            $this->providers[$key] = new AuthManager($app, $key, $config);
        }

        if(session()->has('current_provider')){
          $this->uses(session()->get('current_provider'));
        }else{
          $this->currentProvider = key($this->providers);
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return |null
     */
    public function __call($name, $arguments = array())
    {
      if(array_key_exists($name,$this->providers)){
        return $this->providers[$name];
      }elseif(array_key_exists($this->currentProvider,$this->providers)){
        $provider = $this->providers[$this->currentProvider];
        if(is_callable([$provider,$name])){
          return call_user_func_array([$provider,$name],$arguments);
        }
      }
    }

    public function uses($name){
      if(array_key_exists($name,$this->providers)){
        session()->put(['current_provider'=>$name]);
        $this->currentProvider = session()->get('current_provider');
      }
      return $this;
    }
}
