<?php
namespace DecisionPipeline;

class ExampleDecider implements PipelineDecider
{
    const EXAMPLE_ERROR = 'This is not a deciding class';
    
    public function decide(Question $question, Decision $decision, callable $next = null)
    {
        throw new \RuntimeException(self::EXAMPLE_ERROR);
    }
}
