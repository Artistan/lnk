<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    private $showDistance=true;
    private $opSheet=true;
    private $fakesSheet=true;
    private $playerInfo=true;
    private $getClose=true;
    private $allianceInfo=true;
    private $inputs = array();
    private $alliances = array();
    private $players = array();
    private $habitats = array();
    private $closest = array();
    private $processed = array();
    private $origin = array();
    private $defaultOriginX = 0;
    private $defaultOriginY = 0;
    // override the elasticsearch type for a type name...
    private $typeOverride = array(
        'closest'=>'habitats'
    );

    public function search()
    {
        $this->inputs = Input::all();
        //echo "<pre>";var_dump($this->inputs);exit;

        $this->setup();
        $this->origin();
        //echo "<pre>";var_dump($this->origin);exit;
        $this->habitats();
        //echo "<pre>";var_dump($this->habitats);exit;
        $this->origin();

        if($this->allianceInfo){
            $this->alliances();
            //echo "<pre>";var_dump($this->alliances);exit;
        }
        if($this->playerInfo || $this->getClose){
            $this->players();
        }
        //echo "<pre>";var_dump($this->players);exit;

        return View::make('search')
            ->with('inputs', $this->inputs)
            ->with('origin', $this->origin)
            ->with('habitats', $this->habitats)
            ->with('players', $this->players)
            ->with('alliances', $this->alliances);
    }

    private function origin(){
        $q = $this->origin_query();
        //print_r($q);exit;
        $json = shell_exec($q);
        $this->origin=$this->data($json);
        if(is_array($this->origin)){
            $this->origin = current($this->origin);
        }
    }

    private function habitats(){
        $this->get('habitats');
        //var_dump($this->habitats);
        if($this->getClose){
            foreach($this->habitats as $hId=>$data){
                $this->inputs['closest']['originY'] = $data['mapY'];
                $this->inputs['closest']['originX'] = $data['mapX'];
                $this->get('closest');
                //echo ($this->inputs['closest']['query_string']);
                //var_dump($this->closest);
                $this->habitats[$hId]['closest'] = $this->closest;
            }
        }
    }

    private function alliances(){
        $this->get('alliances');
    }

    private function players(){
        $this->get('players');
    }

    private function get($type){
        if(empty($this->processed[$type])){
            $this->process($type);// only process once.
            $this->processed[$type]=true;
        }
        $this->inputs[$type]['query']  =  $this->query_parts($type);

        $q = $this->query($type);
        $json = shell_exec($q);
        $this->$type=$this->data($json);
    }

    private function data(&$json){
        $decode = json_decode($json);
        $data=array();
        if(!empty($decode->hits->hits)) {
            foreach ($decode->hits->hits as $decode) {
                $data[$decode->_id] = array();
                $data[$decode->_id] = (Array) $decode->_source;
                $data[$decode->_id]['_search_score'] = $decode->_score;
            }
        }
        return $data;
    }

    private function setup(){
        if(empty($this->inputs['ops'])){
            $this->opSheet=false;
        }
        if(empty($this->inputs['fakes'])){
            $this->fakesSheet=false;
        }
        if(empty($this->inputs['distance'])){
            $this->showDistance=false;
        }
        if(empty($this->inputs['playerInfo'])){
            $this->playerInfo=false;
        }
        if(empty($this->inputs['getClose'])){
            $this->getClose=false;
        }
        if(empty($this->inputs['allianceInfo'])){
            $this->allianceInfo=false;
        }
        $this->inputs['ops']        = $this->opSheet;
        $this->inputs['fakes']        = $this->fakesSheet;
        $this->inputs['distance']   = $this->showDistance;
        $this->inputs['allianceInfo']   = $this->allianceInfo;
        $this->inputs['playerInfo']   = $this->playerInfo;
        $this->inputs['getClose']   = $this->getClose;
        if(empty($this->inputs['server']) || $this->inputs['server']==125 || $this->inputs['server']=='us-9'){
            $this->inputs['server']=125;
            $this->inputs['index']='us-9';
        } else if( $this->inputs['server']==168 || $this->inputs['server']=='us-12'){
            $this->inputs['server']=168;
            $this->inputs['index']='us-12';
        } else if($this->inputs['server']==113 || $this->inputs['server']=='us-8') {
            $this->inputs['server']=113;
            $this->inputs['index']='us-8';
        }
        // default origin coordinates.
        if($this->inputs['server']==168){
            // my fort on 12
            $this->defaultOriginX=16430;
            $this->defaultOriginY=16382;
        } else if($this->inputs['server']==125){
            // my fort on 9
            $this->defaultOriginX=16413;
            $this->defaultOriginY=16252;
        } else if($this->inputs['server']==113) {
            // my fort on 8
            $this->defaultOriginX=16202;
            $this->defaultOriginY=16518;
        }
        if(empty($this->inputs['originX'])){
            $this->inputs['originX'] = $this->defaultOriginX;
        } else {
            $this->defaultOriginX = $this->inputs['originX'];
        }
        if(empty($this->inputs['originY'])){
            $this->inputs['originY'] = $this->defaultOriginY;
        } else {
            $this->defaultOriginY = $this->inputs['originY'];
        }
    }

    private function origin_query(){
        return '
curl  -H "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7" \'http://localhost:9200/'.$this->inputs['index'].'-habitats/data/_search?pretty\' -d \'
{
    "from" : 0, "size" : 1,
    "query": {
        "bool": {
            "must": [
                {
                    "term": {
                        "mapX" : '.$this->defaultOriginX.'
                    }
                },
                {
                    "term": {
                        "mapY" : '.$this->defaultOriginY.'
                    }
                }
            ]
        }
    }
}\'
';

    }
    private function process($type){
        if(!empty($this->inputs[$type]['min'])){
            $this->inputs[$type]['min']=$this->inputs[$type]['min'];
        } else if(!empty($this->inputs[$type]['max'])){
            $this->inputs[$type]['min']=0;
        }
        if(!empty($this->inputs[$type]['max'])){
            $this->inputs[$type]['max']=$this->inputs[$type]['max'];
        } else if(!empty($this->inputs[$type]['min'])){
            $this->inputs[$type]['max']=20000;
        }
        if(!empty($this->inputs[$type]['points'])){
            if(is_array($this->inputs[$type]['points'])){
                $this->inputs[$type]['terms']['points']=$this->inputs[$type]['points'];
            } else {
                $this->inputs[$type]['terms']['points']=explode(',',$this->inputs[$type]['points']);
            }
        }
        if($type=='habitats' || $type=='closest'){

            if(empty($this->inputs[$type]['players'])){
                $this->inputs[$type]['players']=array();
            }
            if(!empty($this->inputs[$type]['playerIDs'])){
                if(is_array($this->inputs[$type]['playerIDs'])){
                    $this->inputs[$type]['terms']['playerID']=array_merge($this->inputs[$type]['playerIDs'],$this->inputs[$type]['players']);
                } else {
                    $this->inputs[$type]['terms']['playerID']=array_merge(explode(',',$this->inputs[$type]['playerIDs']), $this->inputs[$type]['players']);
                }
            } else if (!empty($this->inputs[$type]['players'])){
                $this->inputs[$type]['terms']['playerID'] = $this->inputs[$type]['players'];
            }
            if(!empty($this->inputs[$type]['terms']['playerID'])){
                $this->inputs[$type]['playerIDs'] = implode(',',$this->inputs[$type]['terms']['playerID']);
                $this->inputs[$type]['players'] = $this->inputs[$type]['terms']['playerID'];
            }
            if(empty($this->inputs[$type]['alliances'])){
                $this->inputs[$type]['alliances']=array();
            }
            if(!empty($this->inputs[$type]['alliancesIDs'])){
                if(is_array($this->inputs[$type]['alliancesIDs'])){
                    $this->inputs[$type]['terms']['allianceID']=array_merge($this->inputs[$type]['alliancesIDs'],$this->inputs[$type]['alliances']);
                } else {
                    $this->inputs[$type]['terms']['allianceID']=array_merge(explode(',',$this->inputs[$type]['alliancesIDs']), $this->inputs[$type]['alliances']);
                }
            } else if (!empty($this->inputs[$type]['alliances'])){
                $this->inputs[$type]['terms']['allianceID'] = $this->inputs[$type]['alliances'];
            }
            if(!empty($this->inputs[$type]['terms']['allianceID'])){
                $this->inputs[$type]['alliancesIDs'] = implode(',',$this->inputs[$type]['terms']['allianceID']);
                $this->inputs[$type]['alliances'] = $this->inputs[$type]['terms']['allianceID'];
            }
            if(empty($this->inputs[$type]['size'])){
                $this->inputs[$type]['size']=100;
            }
            if(!empty($this->inputs[$type]['in_alliance'])){
                if($this->inputs[$type]['in_alliance'] == 'no'){
                    $this->inputs[$type]['missing'][]='allianceID';
                }
                if($this->inputs[$type]['in_alliance'] == 'yes'){
                    $this->inputs[$type]['exists'][]='allianceID';
                }
            }
            // if default set and type not set, then use the default.
            if(!empty($this->inputs['originX']) && empty($this->inputs[$type]['originX'])){
                $this->inputs[$type]['originX'] = $this->inputs['originX'];
            }
            // if default set and type not set, then use the default.
            if(!empty($this->inputs['originY']) && empty($this->inputs[$type]['originY'])){
                $this->inputs[$type]['originY'] = $this->inputs['originY'];
            }
        } else {
            if(empty($this->inputs[$type]['size'])){
                $this->inputs[$type]['size']=9999999;
            }
        }
        //echo "<pre>";var_dump($this->inputs[$type]);exit;
    }

    private function query_parts($type="habitats"){
        $checks = array();
        $query='';
        if(!empty($this->inputs[$type]['should'])){
            if(is_array($this->inputs[$type]['should'])){
                foreach($this->inputs[$type]['should'] as $tag=>$values){
                    $qt='';
                    if(!is_numeric(current($values))){
                        $qt='"';
                    }
                    foreach($values as $id){
                        $checks['bool']['should'][]='
                        {
                            "term": {
                                "'.$tag.'" : '.$qt.$id.$qt.'
                            }
                        }
                    ';
                    }
                }
            }

        }
        if(!empty($this->inputs[$type]['must'])){
            if(is_array($this->inputs[$type]['must'])){
                foreach($this->inputs[$type]['must'] as $tag=>$values){
                    $qt='';
                    if(!is_numeric(current($values))){
                        $qt='"';
                    }
                    foreach($values as $id){
                        $checks['bool']['must'][]='
                        {
                            "term": {
                                "'.$tag.'" : '.$qt.$id.$qt.'
                            }
                        }
                    ';
                    }
                }
            }

        }
        if(!empty($this->inputs[$type]['must_not'])){
            if(is_array($this->inputs[$type]['must_not'])){
                foreach($this->inputs[$type]['must_not'] as $tag=>$values){
                    $qt='';
                    if(!is_numeric(current($values))){
                        $qt='"';
                    }
                    foreach($values as $id){
                        $checks['bool']['must_not'][]='
                        {
                            "term": {
                                "'.$tag.'" : '.$qt.$id.$qt.'
                            }
                        }
                    ';
                    }
                }
            }

        }

        $boolParts = array();
        if(!empty($checks['bool']['should'])){
            $boolParts[] = '"should": [ '.implode(',',$checks['bool']['should']).' ]';
        }
        if(!empty($checks['bool']['must'])){
            $boolParts[] = '"must": [ '.implode(',',$checks['bool']['must']).' ]';
        }
        if(!empty($checks['bool']['must_not'])){
            $boolParts[] = '"must_not": [ '.implode(',',$checks['bool']['must_not']).' ]';
        }

        if($filter = $this->filter($type)){
            $boolParts[] = '"filter": [ '.$filter.' ]';
        }

        if(!empty($boolParts)){
            $query .= '
            "bool": {
                '.implode(',',$boolParts).'
            }
            ';
        }
        return $query;
    }

    private function filter($type="habitats"){
        $checks=array();
        if(isset($this->inputs[$type]['min']) && !empty($this->inputs[$type]['max'])){
            $checks[] = '{
                            "range": {
                                "points": {
                                    "gte" : '.$this->inputs[$type]['min'].',
                                    "lte" : '.$this->inputs[$type]['max'].'
                                }
                            }
                        }';
        }
        if(!empty($this->inputs[$type]['terms'])){
            if(is_array($this->inputs[$type]['terms'])){
                foreach($this->inputs[$type]['terms'] as $tag=>$values){
                    if(is_numeric(current($values))){
                        $qt='';
                    } else {
                        $qt='"';
                    }
                    $checks[] = '
                          {"terms": {
                            "'.$tag.'": ['.$qt.implode($qt.','.$qt,$values).$qt.']
                          }}
                    ';
                }
            }
        }
        if(!empty($this->inputs[$type]['missing'])){
            if(is_array($this->inputs[$type]['missing'])){
                foreach($this->inputs[$type]['missing'] as $tag){
                    $checks[]='
                            { "missing" : { "field" : "'.$tag.'" } }
                    ';
                }
            }
        }
        if(!empty($this->inputs[$type]['exists'])){
            if(is_array($this->inputs[$type]['exists'])){
                foreach($this->inputs[$type]['exists'] as $tag){
                    $checks[]='
                            { "exists" : { "field" : "'.$tag.'" } }
                    ';
                }
            }
        }

        if(!empty($checks)){
            return implode(',',$checks);
        }
        return false;
    }

    private function query($type){
        if(empty($this->inputs[$type]['query'])){
            $this->inputs[$type]['query'] = '"match_all": {}';
        }
        // distance queries....
        if(!empty($this->inputs[$type]['originX'])){
            $query = '
            "query": {
                "function_score":{
                    "query":  {
                        '.$this->inputs[$type]['query'].'
                    },
                    "boost_mode": "replace",
                    "script_score" : {
                        "script" : {
                            "params" : {
                                "originX" : '.$this->inputs[$type]['originX'].',
                                "originY" : '.$this->inputs[$type]['originY'].'
                            },
                            "inline": "double abs(def x) { if(x < 0){return -x;} return x; } double paramsPossitionOneY = params.originY & 1; double docPossitionOneY = doc[\"mapY\"].value & 1; double originX = params.originX; double docX = doc[\"mapX\"].value;  if(paramsPossitionOneY>0){     originX = params.originX + 0.5 } if(docPossitionOneY>1){     docX = doc[\"mapX\"].value + 0.5; }  double xyDistance = abs(params.originY - doc[\"mapY\"].value); double xxDistance = abs( originX - docX ); double xyHalfDistance = xyDistance * 0.5;  if( xyHalfDistance >= xxDistance) {     return xyDistance; } else {     return (xyHalfDistance + xxDistance); }"
                        }
                    }
                }
            },
            ';
        } else {
            $query = '
            "query":  {
                '.$this->inputs[$type]['query'].'
            },
            ';
        }

        if(!empty($this->typeOverride[$type])){
            $elasticType = $this->typeOverride[$type];
        } else {
            $elasticType = $type;
        }

        $this->inputs[$type]['query_string'] = '
curl  -H "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7" \'http://localhost:9200/'.$this->inputs['index'].'-'.$elasticType.'/data/_search?pretty\' -d \'
{
    "from" : 0, "size" : '.$this->inputs[$type]['size'].',
    '.$query.'
    "sort" : [
        {
            "_score" : {
                "order": "asc"
            }
        }
    ]
}\'
';
        //print_r($this->inputs[$type]['query_string']);exit;
        return $this->inputs[$type]['query_string'];
    }
}
