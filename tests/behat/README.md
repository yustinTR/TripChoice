Find documentation at https://wiki.finalist.com/display/DRP/Behat

# DDEV
You can start the behat tests on ddev with the `ddev behat <featurename>` command.
For instance `ddev behat features/smoketest.feature` to run the smoketests.

# Selenium info

## Install browser on jenkins node
`sudo apt install chromium-browser`

## Run selenium in docker on jenkins node
`docker run --rm -d --network host -p 4444:4444 -v /dev/shm:/dev/shm --name drupal-selenium selenium/standalone-chrome:2.53.1`

## Stop selenium in docker on jenkins node
`docker stop drupal-selenium`

## Kill selenium on port 4444
`lsof -t -i :4444 | xargs kill`
