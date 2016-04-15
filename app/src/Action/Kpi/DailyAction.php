<?php

namespace App\Action\Kpi;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Thapp\XmlBuilder\XmlBuilder;
use Thapp\XmlBuilder\Normalizer;
use FileSystemCache;

final class DailyAction
{
    private $view;
    private $logger;
    private $url = 'http://conteudo.farolsign.com.br/custom/nivea/indicadores/json/diario.php';

    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $data = array();

        $content = @file_get_contents($this->url);

        if($content !== false)
        {
            $json = json_decode($content, true);

            $data['info'] = array(
                'publishedat' => 'Atualizado em ' . $json['Data'] . ' às ' . $json['Hora'],
            );

            for($i = 1; $i <= 4; $i++)
            {
                $data['lines']['fl' . $i] = $json['FL' . $i];
            }

            $data['lines']['geral'] = $json['Geral'];

            foreach($data['lines'] as $key => $lines)
            {
                unset($data['lines'][$key]);

                $name = substr($key, 0, 2) == 'fl' ? str_replace('fl', 'linha ', $key) : $key;

                $data['lines'][] = array(
                    'name' => $name,
                    'oae' => str_replace(',', '.', substr($lines['OAE'], 0, -1)),
                    'units' => str_replace('.', '', $lines['Unidades']),
                    'breakdown' => array(
                        'time' => $lines['TempoBreakdown'],
                        'percent' => str_replace(',', '.', substr($lines['PorcentagemBreakdown'], 0, -1)),
                    ),
                    'microstoppage' => array(
                        'time' => $lines['TempoMicrostoppage'],
                        'percent' => str_replace(',', '.', substr($lines['PorcentagemMicrostoppage'], 0, -1)),
                    )
                );
            }

            $data['elapsed'] = array(
                'time' => $json['TempoDecorrido'],
                'percent' => str_replace(',', '.', substr($json['PorcentagemDecorrido'], 0, -1)),
            );

            FileSystemCache::$cacheDir = __DIR__ . '/../../../../cache/tmp';
            $key = FileSystemCache::generateCacheKey('cache-feed_monthy', null);
            FileSystemCache::store($key, $data);

        }
        else
        {
            FileSystemCache::$cacheDir = __DIR__ . '/../../../../cache/tmp';
            $key = FileSystemCache::generateCacheKey('cache-feed_monthy', null);
            $data = FileSystemCache::retrieve($key);
        }

        $xmlBuilder = new XmlBuilder('root');

        $xmlBuilder->setSingularizer(function ($name) {
            if ('lines' === $name) {
                return 'line';
            }
            return $name;
        });

        $xmlBuilder->load($data);

        $xml_output = $xmlBuilder->createXML(true);

        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text/xml');
        return $response;
    }
}