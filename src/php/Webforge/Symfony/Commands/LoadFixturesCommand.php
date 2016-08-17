<?php

namespace Webforge\Symfony\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LoadFixturesCommand extends ContainerAwareCommand {

  private $providers;

  public function __construct(array $providers) {
    parent::__construct();
    $this->providers = $providers;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('testing:load-alice-fixtures')
      ->setDescription('Inserts all alice fixtures into the db.')
      ->addOption('purge', null, InputOption::VALUE_NONE, 'Purge the db before inserting.')
      ->addArgument('files', InputArgument::IS_ARRAY, 'List of files to import.')
      ->addOption('manager', 'm', InputOption::VALUE_OPTIONAL, 'The fixture manager name to used.', 'default');
    ;
  }

  protected function findFiles(Array $files) {
    return $files;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $objectManager = $this->getContainer()->get(sprintf('doctrine.orm.%s_entity_manager', $input->getOption('manager')));

    if ($input->getOption('purge')) {
      $this->purge($objectManager, $output);  
    }

    $persister = new \Nelmio\Alice\Persister\Doctrine($objectManager, $doFlush = FALSE);
    $loader = new \Nelmio\Alice\Fixtures\Loader();
    foreach ($this->providers as $provider) {
      $loader->addProvider($provider);
    }

    $output->writeln('persisting...');
    foreach ($this->findFiles($input->getArgument('files')) as $file) {
      $objects = $loader->load($file);
      $output->writeln('<comment>  file: '.$file.'</comment>');
      $persister->persist($objects);
    }

    $output->writeln('flushing...');
    $objectManager->flush();
    $output->writeln('<info>done.</info>');
    return 0;
  }

  protected function purge($objectManager, OutputInterface $output) {
    $output->writeln('purging...');
    $connection = $objectManager->getConnection();
    $platform = $connection->getDatabasePlatform();
    $configuration = $objectManager->getConfiguration();

    $connection->executeQuery('set foreign_key_checks = 0');
    foreach ($objectManager->getMetadataFactory()->getAllMetadata() as $class) {
      if ($this->isTabledMetadata($class)) {
        continue;
      }

      $tbl = $this->getTableName($class, $platform, $configuration);
      $connection->executeUpdate($platform->getTruncateTableSQL($tbl, true));
    }
    $connection->executeQuery('set foreign_key_checks = 1');
  }

  private function isTabledMetadata($class) {
    if (isset($class->isEmbeddedClass) && $class->isEmbeddedClass) {
      return TRUE;
    }

    if ($class->isMappedSuperclass) {
      return TRUE;
    }
    
    if ($class->isInheritanceTypeSingleTable() && $class->name !== $class->rootEntityName) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   *
   * @param \Doctrine\ORM\Mapping\ClassMetadata $class
   * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
   * @return string
   */
  private function getTableName($class, $platform, $configuration) {
    if (isset($class->table['schema']) && !method_exists($class, 'getSchemaName')) {
      return $class->table['schema'].'.'.$configuration->getQuoteStrategy()->getTableName($class, $platform);
    }
    return $configuration->getQuoteStrategy()->getTableName($class, $platform);
  }
}
