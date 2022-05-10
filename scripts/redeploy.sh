#!/usr/bin/env bash

prod_branch="master"

if [ "$1" == "$prod_branch" ]; then
    # Saves uncommitted changes and reset repo
    git stash
    git reset --hard

    # Move to master branch
    git checkout master

    # Pull latest commits from master
    git pull origin master
fi