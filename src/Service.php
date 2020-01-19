<?php

namespace Cajudev\RestfulApi;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

abstract class Service
{
    protected $em;

    public function __construct()
    {
        $this->em = EntityManager::getInstance();
    }

    public function toJson(Response $response, array $content): Response
    {
        $response->getBody()->write(json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $response;
    }

    public function getOne(Request $request, Response $response, array $args): Response
    {
        $validator = $this->getValidator($args);
        $validator->validateRead();

        $entity = $this->getRepository()->find($args['id']);
        return $this->toJson($response, ['success' => true, 'data' => $entity->toArray()]);
    }

    public function getAll(Request $request, Response $response, array $args): Response
    {
        $entities = $this->getRepository()->findAll();
        return $this->toJson($response, ['success' => true, 'data' => $entities->toArray()]);
    }

    public function insert(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody() ?? [];

        $validator = $this->getValidator($params);
        $validator->validateInsert();

        $entity = $this->getEntity($validator->getData());

        $this->em->persist($entity);
        $this->em->flush();

        return $this->toJson($response, ['success' => true, 'data' => $entity->toArray()]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $params = $request->getParsedBody() ?? [];
        $params['id'] = $args['id'];

        $validator = $this->getValidator($params);
        $validator->validateUpdate();

        $params = $validator->getData();
        $entity = $this->getRepository()->find($params['id']);
        $entity->setParams($params);

        $this->em->flush();

        return $this->toJson($response, ['success' => true, 'data' => $entity->toArray()]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $params['id'] = $args['id'];

        $validator = $this->getValidator($params);
        $validator->validateDelete();

        $params = $validator->getData();
        $entity = $this->getRepository()->find($params['id']);

        $return = $entity->toArray();

        $this->em->remove($entity);
        $this->em->flush();

        return $this->toJson($response, ['success' => true, 'data' => $return]);
    }

    public function getEntity(array $params = [])
    {
        $class = str_replace('Services', 'Entity', static::class);
        return new $class($params);
    }

    public function getValidator(array $params = [])
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
