<?php

namespace App\Command;

use App\Entity\feedContent;
use App\Repository\FeedContentRepository;
use App\Repository\FeedRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

/**
 * A console command that parse feed URL and retrive details then saves them in the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:extract-feed
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:extract-feed -vv
 *
 * @author Nikunj Bambhroliya <nikunjpatl190@gmail.com>
 */
class FeedExtractCommand extends Command
{
    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'app:extract-feed';

    /**
     * @var SymfonyStyle
     */
    private $io;

    private $entityManager;
    private $feedContent;
    private $slugger;
    private $feeds;

    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger, FeedContentRepository $feedContent, FeedRepository $feeds)
    {
        parent::__construct();

        $this->entityManager = $em;
        $this->slugger = $slugger;
        $this->feedContent = $feedContent;
        $this->feeds = $feeds;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generate Feed content from feed URL')
            ->setHelp($this->getCommandHelp());
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('feed-extract-command');

        $feeds = $this->feeds->findBy(['status' => "active", "isDummy" => 0]);

        if(count($feeds) > 0){
            foreach ($feeds AS $feed){
                $feedId = $feed->getId();
                $feedUrl = $feed->getUrl();

                //$objXmlDocument = simplexml_load_file($feedUrl);

                $objXmlDocument = new \DOMDocument();
                $objXmlDocument->load($feedUrl);

                $items = $objXmlDocument->getElementsByTagName('item');

                //if($objXmlDocument && $objXmlDocument->channel && $objXmlDocument->channel->item){
                if(count($items) > 0){
                    //foreach ($objXmlDocument->channel->item AS $item){
                    foreach ($items AS $item){
                        $guid = $item->getElementsByTagName('guid')->item(0)->nodeValue;
                        $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
                        $slug = $this->slugger->slug($title)->lower();
                        $feedContent = $this->feedContent->findOneBy(['guid' => $guid]);
                        if(!$feedContent) {
                            $feedContent = new feedContent();
                            $feedContent->setCreatedAt(new \DateTime());
                        } else {
                            $feedContent->setUpdatedAt(new \DateTime());
                        }

                        $feedContent->setFeed($feed);
                        $feedContent->setSlug($item->getElementsByTagName('title')->item(0)->nodeValue);
                        $feedContent->setTitle($title);
                        $feedContent->setSlug($slug);
                        $feedContent->setContent($item->getElementsByTagName('description')->item(0)->nodeValue);
                        $feedContent->setUrl($item->getElementsByTagName('link')->item(0)->nodeValue);
                        $feedContent->setPublishedAt($item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                        $feedContent->setGuid($guid);
                        $feedContent->setStatus('active');
                        $feedContent->setAuthor($item->getElementsByTagName('creator')->item(0)->nodeValue);

                        $this->entityManager->persist($feedContent);
                        $this->entityManager->flush();

                        $this->io->success(sprintf('Feed successfully successfully for GUID : %s', $guid));
                    }
                }
            }
        } else {
            throw new RuntimeException("No any active feeds found!");
        }

        $event = $stopwatch->stop('feed-extract-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('Elapsed time: %.2f ms / Consumed memory: %.2f MB',$event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
                    The <info>%command.name%</info> command will parse feed URL and retrive details saves them in the <database class=""></database>
HELP;
    }
}
