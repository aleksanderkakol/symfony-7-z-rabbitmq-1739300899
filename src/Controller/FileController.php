<?php

declare(strict_types=1);

namespace App\Controller;

use App\MessageBus\Command\UploadUserList\UploadUserListCommand;
use App\MessageBus\Query\GetFileErrorsQuery\GetFileErrorsQuery;
use App\MessageBus\Query\GetFileProgressQuery\GetFileProgressQuery;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/', name: 'csv_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('upload/csv/upload.html.twig');
    }

    #[Route(path: '/upload', name: 'csv_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if ($file) {
            $fileUuid = Uuid::v7();
            $fileName = $this->createFileName($file, $fileUuid);
            $uploadsPath = $this->getUploadsPath();

            $file->move($uploadsPath, $fileName);

            $this->commandBus->dispatch(new UploadUserListCommand($uploadsPath, $fileName));

            return $this->redirectToRoute('csv_process', ['fileName' => $fileName]);
        }

        return $this->redirectToRoute('csv_index');
    }

    #[Route('/process/{fileName}', name: 'csv_process')]
    public function process(string $fileName): Response
    {
        return $this->render('upload/csv/process.html.twig', ['fileName' => $fileName]);
    }

    #[Route('/errors/{fileName}', name: 'csv_errors')]
    public function errors(string $fileName): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(
            new GetFileErrorsQuery(fileName: $fileName),
        );
        $errors = $envelope->last(HandledStamp::class)?->getResult();

        return new JsonResponse(['errors' => $errors]);
    }

    #[Route('/progress/{fileName}', name: 'csv_progress')]
    public function progress(string $fileName): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(
            new GetFileProgressQuery(fileName: $fileName),
        );
        $progress = $envelope->last(HandledStamp::class)?->getResult();

        return new JsonResponse(['progress' => round((float) $progress, 2)]);
    }

    private function createFileName(UploadedFile $file, Uuid $uuid): string
    {
        $type = $file->guessExtension();
        if ($type === null) {
            $type = 'csv';
        }
        return sprintf('%s.%s', (string) $uuid, $type);
    }

    private function getUploadsPath(): string
    {
        return sprintf('%s/uploads/', '/tmp');
    }

}
