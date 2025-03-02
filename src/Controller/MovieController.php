<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieController extends AbstractController
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['TMDB_API_KEY'];
    }
    #[Route('/', name: 'homepage')]
    public function home(): Response
    {
        return $this->redirectToRoute('movies_list');
    }

    #[Route('/movies/{id}', name: 'movie_detail')]
    public function show(int $id): Response
    {
        // Appel à l'API TMDB pour récupérer les détails du film
        $response = $this->client->request(
            'GET',
            "https://api.themoviedb.org/3/movie/{$id}",
            [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => 'fr-FR',
                ],
            ]
        );

        $movie = $response->toArray();

        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/movies', name: 'movies_list')]
    public function index(Request $request): Response
    {
        $query = $request->query->get('search', ''); // Recherche par défaut : "batman"

        // Appel à l'API TMDB
        $response = $this->client->request(
            'GET',
            'https://api.themoviedb.org/3/search/movie',
            [
                'query' => [
                    'api_key' => $this->apiKey,
                    'query' => $query,
                    'language' => 'fr-FR',
                ],
            ]
        );

        $movies = $response->toArray();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies['results'] ?? [],
            'query' => $query,
        ]);
    }
}
