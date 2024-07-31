<?php
declare(strict_types=1);

namespace App\Handler;

use App\InputFilter\CategoryInputFilter;
use App\Service\TransactionCategoriesService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateTransactionCategoryHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TransactionCategoriesService $service,
        private readonly CategoryInputFilter $inputFilter
    ) {}


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setValidationGroup(["name", "parent"]);
        $this->inputFilter->setData($request->getParsedBody());

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $data = $this->inputFilter->getValues();

        try {
            $id = $this->service->create($data['name'], $data['parent']);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['error' => "Entity not found"], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse(['id' => (string) $id], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
