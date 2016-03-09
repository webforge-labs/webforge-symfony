# webforge-symfony
A bridge for webforge and symfony with the usual suspects

## Use the DateTimeHandler

```
composer require webforge/symfony
```

add to your `config.yml`/`services.yml` from symfony:
```yaml
imports:
    - { resource: '../../vendor/webforge/symfony/Resources/config/services.yml' }
```

when you're using the [webforge/doctrine-compiler](https://github.com/webforge-labs/webforge-doctrine-compiler) with serializer extension your all set for WebforgeDateTime-Types. Otherwise use annotations like this:

```php
<?php
  
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

class EntityWithTimestamp {

  /**
   * modified timestamp saves the time and date of the last modification
   * 
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime", nullable=true)
   * @Serializer\Expose
   * @Serializer\Type("WebforgeDateTime")
   */
  protected $modified = NULL;


  public function updateModified() {
    $this->modified = \Webforge\Common\DateTime\DateTime::now();
  }
}

```
