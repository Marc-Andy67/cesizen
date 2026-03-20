<?php

require 'vendor/autoload.php';
$r = new ReflectionMethod(Symfony\Component\Security\Core\Authorization\Voter\Voter::class, 'voteOnAttribute');
echo $r;
