<?php



class Fps_Viewer_Node_Expresion
{

    protected $filters = array();



    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }



    public function parseFilters(Fps_Viewer_CompileParser $compiler)
    {

        if (is_array($this->filters) && count($this->filters)) {
            $filter = array_pop($this->filters);
            $this_ = $this;
            $filter->compile(function($compiler) use ($this_){
                $this_->compile($compiler);
            }, $compiler);
        } else {
            $this->compile($compiler);
        }
    }
}