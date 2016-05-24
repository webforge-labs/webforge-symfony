<?php

namespace Webforge\Symfony;

use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Webforge\Common\System\Dir;

class Kernel extends SymfonyKernel
{
    /**
     * Bootstrap code from project
     *
     * this is called from a project that has the cms installed, from its own bootstrap:
     *
     * ```php
     *     $loader = require __DIR__.'/vendor/autoload.php';
     *     return Webforge\CmsBundle\Kernel::bootstrap(__DIR__, $loader);
     * ```
     *
     * this keeps the bootstrap from installed edition very small and gives us power to refactor the bootstrapping process (been there with psc-cms)
     * 
     * @param  string $rootDir the dir where the project is installed
     * @param  Composer\Autoload\ClassLoader $loader
     * @return mixed
     */
    public static function bootstrap($rootDir, $loader)
    {
        AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

        $GLOBALS['env']['root'] = Dir::factoryTS($rootDir);

        require $GLOBALS['env']['root']->getFile('app/AppKernel.php');

        return $loader;
    }

    public function registerBundles()
    {
        $bundles = array();
        
        if (class_exists('\Symfony\Bundle\FrameworkBundle\FrameworkBundle')) {
            $bundles[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
        }

        if (class_exists('\Symfony\Bundle\SecurityBundle\SecurityBundle')) {
            $bundles[] = new \Symfony\Bundle\SecurityBundle\SecurityBundle();
        }

        if (class_exists('\Symfony\Bundle\TwigBundle\TwigBundle')) {
            $bundles[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
        }

        if (class_exists('\Symfony\Bundle\MonologBundle\MonologBundle')) {
            $bundles[] = new \Symfony\Bundle\MonologBundle\MonologBundle();
        }

        if (class_exists('\Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle')) {
            $bundles[] = new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle();
        }

        if (class_exists('\Doctrine\Bundle\DoctrineBundle\DoctrineBundle')) {
            $bundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
        }

        if (class_exists('\Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle')) {
            $bundles[] = new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
        }

        if (class_exists('\Webforge\CmsBundle\WebforgeCmsBundle')) {
            $bundles[] = new \Webforge\CmsBundle\WebforgeCmsBundle();
        }

        if (class_exists('\Liip\ImagineBundle\LiipImagineBundle')) {
            $bundles[] = new \Liip\ImagineBundle\LiipImagineBundle();
        }

        if (class_exists('\JMS\SerializerBundle\JMSSerializerBundle')) {
            $bundles[] = new \JMS\SerializerBundle\JMSSerializerBundle();
        }

        if (class_exists('\FOS\UserBundle\FOSUserBundle')) {
            $bundles[] = new \FOS\UserBundle\FOSUserBundle();
        }

        if (class_exists('\Knp\Bundle\GaufretteBundle\KnpGaufretteBundle')) {
            $bundles[] = new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle();
        }

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            //$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            //$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new \h4cc\AliceFixturesBundle\h4ccAliceFixturesBundle();
            $bundles[] = new \Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }
    
    public function getCacheDir()
    {
        return $this->rootDir.'/../files/cache/symfony-'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->rootDir.'/../files/logs/symfony';
    }

    protected function getEnvParameters() 
    {
        $env = parent::getEnvParameters();

        // please note: this is executed BEFORE the config is loaded, so these parameters are just a default
        // => everything is overwritable by the config.yml
        return array_merge(
          $env,
          array(
              'root_directory'=>$GLOBALS['env']['root']->wtsPath()
          )
        );
    }


    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/../etc/symfony/config_'.$this->getEnvironment().'.yml');
    }
}
