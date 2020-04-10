<?php

namespace Cajudev\Rest;

use Cajudev\Rest\CriteriaBuilder;
use Cajudev\Rest\Annotations\Query;
use Cajudev\Rest\Factories\EntityFactory;
use Cajudev\Rest\Factories\ValidatorFactory;
use Cajudev\Rest\Factories\RepositoryFactory;
use Cajudev\Rest\Annotations\AnnotationManager;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Service
{
    protected $em;
    protected $name;

    public function __construct()
    {
        $this->em = EntityManager::getInstance();
        $this->name = (new \ReflectionClass($this))->getShortName();
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $validator = ValidatorFactory::make($this->name, $args);
        $validator->validate(Validator::READ);

        $entity = RepositoryFactory::make($this->name)->find($args['id']);
        return $this->toJson($response, $entity->payload())->withStatus(200);
    }

    public function all(Request $request, Response $response, array $args): Response
    {
        $entities = RepositoryFactory::make($this->name)->findAll();
        return $this->toJson($response, ['data' => $entities->payload(), 'total' => $entities->count()])->withStatus(200);
    }

    public function search(Request $request, Response $response, array $args): Response
    {
        $manager = new AnnotationManager(EntityFactory::make($this->name));
        $properties = $manager->getAllPropertiesWith(Query::class);

        $query = $request->getQueryParams();
        $query['sortables'] = array_keys(array_filter($properties, fn($property) => $property->sortable));
        $query['searchables'] = array_keys(array_filter($properties, fn($property) => $property->searchable));

        $builder = new CriteriaBuilder($query);
        [$counter, $criteria] = $builder->build();

        $repository = RepositoryFactory::make($this->name);
        $counter = $repository->matching($counter);
        $entities = $repository->matching($criteria);
        
        return $this->toJson($response, ['data' => $entities->payload(), 'total' => $counter->count()])->withStatus(200);
    }

    public function insert(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody() ?? [];

        $validator = ValidatorFactory::make($this->name, $params);
        $validator->validate(Validator::INSERT);

        $entity = EntityFactory::make($this->name, $validator->payload());

        $this->em->persist($entity);
        $this->em->flush();

        return $this->toJson($response, $entity->payload())->withStatus(201);
        ;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody() ?? [];
        $params->id = $args['id'];

        $validator = ValidatorFactory::make($this->name, $params);
        $validator->validate(Validator::UPDATE);

        $params = $validator->payload();
        $entity = RepositoryFactory::make($this->name)->find($params->id);
        $entity->populate($params);

        $this->em->flush();

        return $this->toJson($response, $entity->payload())->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $validator = ValidatorFactory::make($this->name, $args);
        $validator->validate(Validator::DELETE);

        $params = $validator->payload();
        $entity = RepositoryFactory::make($this->name)->find($args['id']);
        $entity->excluded = true;

        $this->em->flush();

        return $response->withStatus(204);
    }

    public function toJson(Response $response, $content): Response
    {
        $response->getBody()->write(json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $response;
    }
}
