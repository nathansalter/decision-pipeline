<?php
namespace DecisionPipeline;

interface Pipeline
{
    /**
     * Take a question and return a decision
     * 
     * @param Question $question
     * @return Decision
     */
    public function decide(Question $question) : Decision;
}
