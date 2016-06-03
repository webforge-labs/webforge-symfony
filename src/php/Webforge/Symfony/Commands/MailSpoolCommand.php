<?php

namespace Webforge\Symfony\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MailSpoolCommand extends Command {

  protected $spoolPath;

  public function __construct($spoolPath) {
    parent::__construct();
    $this->spoolPath = $spoolPath;

  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('mail:spool')
      ->setDescription('Returns all mails currently spooled.')
      ->setDefinition(array(
        new InputOption(
          'clear', null, InputOption::VALUE_NONE,
          'Clears the whole spool (!).'
        )
      ))
    ;
  }

  protected function getFinder() {
    return Finder::create()->files()->in($this->spoolPath);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    if ($input->getOption('clear')) {
      if (is_dir($this->spoolPath)) {
        foreach($this->getFinder() as $file) {
          unlink($file);
        }
      }

      return 0;
    }

    $getters = array(
      'subject'=>'getSubject',
      'returnPath'=>'getReturnPath',
      'sender'=>'getSender',
      'from'=>'getFrom',
      'replyTo'=>'getReplyTo',
      'to'=>'getTo',
      'cc'=>'getCc',
      'bcc'=>'getBcc',
      'body'=>'getBody'
    );

    $result = array();
    foreach ($this->getFinder() as $mailFile) {
      /** @var $message \Swift_Message */
      $message = unserialize($mailFile->getContents());

      $export = new \stdClass;

      foreach ($getters as $var => $getter) {
        $export->$var = $message->$getter();
      }

      $export->fullFrom = $export->from;
      $export->from = current(array_keys($export->fullFrom));

      $export->headers = array();
      foreach ($message->getHeaders()->getAll() as $header) {
        $export->headers[$header->getFieldName()] = $header->getFieldBody();
      }

      if ($export->headers['X-Swift-To']) {
        $export->to = $export->headers['X-Swift-To'];
      }

      $result[] = $export;
    }

    $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
  }
}
