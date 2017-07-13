# Testing

### PHPUnit

To setup tests

- Git clone VVV: `git clone git@github.com:Varying-Vagrant-Vagrants/VVV.git`
- Navigate into the `www` folder and git clone timber `git clone git@github.com:timber/timber.git`
- Login to Vagrant SSH: `vagrant ssh`
- Navigate to your Timber folder `cd /srv/www/timber`
- Install dependencies `composer install`
- Run PHPUnit! `phpunit`

##### Full code

```
cd ~/Sites
git clone git@github.com:Varying-Vagrant-Vagrants/VVV.git
cd VVV/www
git clone git@github.com:timber/timber.git
vagrant ssh
cd /srv/www/timber
composer install
phpunit
```

##### Gotchas!

- You may need to setup authorization between VVV and GitHub. Just follow the prompts to create a token if that interrupts the `composer install`

- You may have [memory problems with Composer](https://getcomposer.org/doc/articles/troubleshooting.md#proc-open-fork-failed-errors) just run this if that happens, here's the script I run:

```
sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
sudo /sbin/mkswap /var/swap.1
sudo /sbin/swapon /var/swap.1
```


