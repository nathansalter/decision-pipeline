<?php
namespace DecisionPipeline;

interface Pipeline
{
    /**
     * @param Question $question
     * @return Decision
     */
    public function decide(Question $question);
}
