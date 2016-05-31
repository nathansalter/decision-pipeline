<?php
namespace DecisionPipeline;

interface PipelineDecider
{
    /**
     * Middleware-esque decision making process. Question will have all of the information, response
     * should be either `return $next()` or return a Decision object
     * 
     * @param Question $question
     * @param Decision $decision
     * @param callable|null $next
     * @return Decision
     */
    public function decide(Question $question, Decision $decision, callable $next = null) : Decision;
}
