# process-queue

Simple throttled process queue using symfony process component and guzzle promises

## Overview

This was developed specifically for running multiple many long running processes asynchronously inside of a throttled
queue. Used to prevent flooding the system with too many simultaneous processes at the same time.

# Usage

You can optionally pass a process limit when creating the ProcessManager or it will default to the number of system
CPUs available if no limit is provided.

    $processFactory = new ProcessFactory('pwd');
    $processManager = new ProcessManager($processFactory);
    
    $promise1 = $processManager->enqueue();
    $promise2 = $processManager->enqueue(new \SplFileInfo('/path/to/working/directory'));
    
    $promise1->then(function(Process $process) {
        // do stuff with the completed process
    });
    
    $promise2->otherwise(function(Process $process) {
        // do stuff with the failed process
    });
    
    // start the queue
    $processManager->run();
    
# Installation

Composer `composer require epfremme/process-queue`
