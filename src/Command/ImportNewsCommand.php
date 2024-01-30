<?php

namespace App\Command;

use App\Entity\News;
use App\Services\ImportHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use SimpleXMLElement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'ImportNews',
    description: 'Add a short description for your command',
)]
class ImportNewsCommand extends Command
{

    private static string $tplDir;

    public function __construct(
        private EntityManagerInterface $em,
        private ImportHelper           $helper,
        private ParameterBagInterface  $parameterBag
    )
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        self::$tplDir = $projectDir . '/templates/data/templates/news/';
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($handle = opendir(self::$tplDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    try {
                        $content = file_get_contents(self::$tplDir . $entry);

                        $content = $this->helper->replaceLinks($content);

                        $news_xml = new SimpleXMLElement($content);
                        $attr = $news_xml->attributes();

//                        $news_id = intval(preg_replace('/^(\d+)\.xml$/', '$1', $entry));
                        $news_order = isset($attr->order) ? intval($attr->order->__toString()) : 0;
                        $news_date = isset($attr->date) ? preg_replace('/^(\d{2})\.(\d{2})\.(\d{4})$/', '$3-$2-$1', $attr->date->__toString()) : null;
                        $news_date = new \DateTime($news_date);
                        $news_title = null;
                        $news_text = null;
                        $news_author = isset($attr->author) ? $attr->author->__toString() : null;
                        $news_author_position = isset($attr->{'author-position'}) ? $attr->{'author-position'}->__toString() : null;
                        $news_tag = isset($attr->tag) ? $attr->tag->__toString() : null;
                        $group_id = isset($attr->group) ? $attr->group->__toString() : null;

                        foreach ($news_xml as $index => $item) {
                            switch ($index) {
                                case 'title':
                                    $news_title = $this->news_title($item);
                                    break;
                                case 'text':
                                    $news_text = $this->news_text($item);
                                    break;
                            }
                        }

                        $news = new News();
//                        $news->setId($news_id);
                        $news->setNewsOrder($news_order);
                        $news->setDate($news_date);
                        $news->setTitle($news_title);
                        $news->setText($news_text);
                        $news->setAuthor($news_author);
                        $news->setAuthorPosition($news_author_position);
                        $news->setTag($news_tag);
                        $news->setGroupId($group_id);

                        $this->em->persist($news);

//                        $sql = "
//                    insert into news (id, news_order, date, title, text, author, author_position, tag)
//                    values ($news_id, $news_order, '$news_date', '$news_title', '$news_text', '$news_author', '$news_author_position', '$news_tag')
//                    on duplicate key update
//                        news_order = $news_order,
//                        date = '$news_date',
//                        title = '$news_title',
//                        text = '$news_text',
//                        author = '$news_author',
//                        author_position = '$news_author_position',
//                        tag = '$news_tag',
//                        group_id = " . ($group_id ? "'$group_id'" : 'NULL') . "
//                    ";
//                        execute_query($sql);
//
//                        echo "$entry<br>";
                        $output->writeln($entry);


                    } catch (Exception $exception) {
                        $output->writeln("Ошибка при импорте файла $entry");
                    }
                }
            }
            $this->em->flush();
            closedir($handle);
        }

        $rows = $this->em->createQueryBuilder()
            ->select('n.id')
            ->from('App:News', 'n')
            ->getQuery()
            ->getScalarResult();

        foreach ($rows as $row) {
            if (!file_exists(self::$tplDir . '/' . $row['id'] . '.xml')) {
                $news = $this->em->getRepository(News::class)->find($row['id']);
                $this->em->remove($news);
                $this->em->flush();
            }
        }

        return Command::SUCCESS;
    }

    protected function news_title($element)
    {
        $title = $element->saveXML();
        $title = str_replace('<title>', '', $title);
        $title = str_replace('</title>', '', $title);
        $title = trim($title);

        return $title;
    }

    protected function news_text($element)
    {
        $text = '';
        foreach ($element as $index => $item) {
            if ($index == 'news_images') {
                $text .= $this->news_text_images($item);
            } else {
                $text .= $item->saveXML();
            }
        }

        return $text;
    }

    protected function news_text_images($element)
    {
        $images = '<div class="news-images">';
        foreach ($element as $index => $item) {
            $images .= '<div class="news-images-item">' .
                '<div class="news-images-item-img" style="background-image: url(/i/' . $item->attributes()->src->__toString() . ');"></div>' .
                '<div class="news-images-item-desc">' . $this->news_text_images_desc($item) . '</div>' .
                '</div>';
        }
        $images .= '</div>';

        return $images;
    }

    protected function news_text_images_desc($element)
    {
        $desc = $element->saveXML();
        $desc = preg_replace('/<news_image src=".*">(.*)<\/news_image>/isU', '$1', $desc);

        return $desc;
    }
}
