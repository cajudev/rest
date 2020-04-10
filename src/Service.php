<?php

namespace Cajudev\Rest;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Cajudev\Rest\CriteriaBuilder;

abstract class Service
{
    protected $em;

    public function __construct()
    {
        $this->em = EntityManager::getInstance();
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $validator = $this->getValidator($args);
        $validator->validateRead();

        $entity = $this->getRepository()->find($args['id']);
        return $this->toJson($response, $entity->payload())->withStatus(200);
    }

    public function all(Request $request, Response $response, array $args): Response
    {
        $entities = $this->getRepository()->findAll();
        return $this->toJson($response, ['data' => $entities->payload(), 'total' => $entities->count()])->withStatus(200);
    }

    public function search(Request $request, Response $response, array $args): Response
    {
        $criteria = new CriteriaBuilder($request->getQueryParams());
        $entities = $this->getRepository()->matching($criteria->build());
        return $this->toJson($response, ['data' => $entities->payload(), 'total' => $entities->count()])->withStatus(200);
    }

    public function insert(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody() ?? [];

        $validator = $this->getValidator($params);
        $validator->validateInsert();

        $entity = $this->getEntity($validator->getData());

        $this->em->persist($entity);
        $this->em->flush();

        return $this->toJson($response, $entity->payload())->withStatus(201);
        ;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody() ?? [];

        $validator = $this->getValidator($params + $args);
        $validator->validateUpdate();

        $params = $validator->getData();
        $entity = $this->getRepository()->find($params['id']);
        $entity->populate($params);

        $this->em->flush();

        return $this->toJson($response, $entity->payload())->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $validator = $this->getValidator($args);
        $validator->validateDelete();

        $params = $validator->getData();
        $entity = $this->getRepository()->find($args['id']);
        $entity->excluded = true;

        $this->em->flush();

        return $response->withStatus(204);
    }

    public function toJson(Response $response, object $content): Response
    {
        $response->getBody()->write(json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $response;
    }

    public function getEntity($params = [])
    {
        $class = str_replace('Services', 'Entity', static::class);
        return new $class($params);
    }

    public function getValidator($params = [])
    {
        $class = str_replace('Services', 'Validator', static::class);
        return new $class($params);
    }

    public function getRepository()
    {
        $class = str_replace('Services', 'Entity', static::class);
        return $this->em->getRepository($class);
    }
}
