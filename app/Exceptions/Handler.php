<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (NotFoundHttpException $e) {
            $this->reportSlack($this->getLogData($e, request()));
        });

        $this->reportable(function (Throwable $e) {
            $this->reportSlack($this->getLogData($e, request()));
        });

        $this->reportable(function (ModelNotFoundException $e, $request) {
            $this->reportSlack($this->getLogData($e, $request));
        });
    }

    private function getLogData($e, $request)
    {
        return [
            'errorInfo' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ],
            'requestInfo' => [
                'url' => $request->url(),
                'method' => $request->method(),
                'input' => $request->all()
            ],
            'user' => [
                'id' => auth()->user()->id ?? null,
                'name' => auth()->user()->name ?? null,
                'emp_id' => auth()->user()->emp_id ?? null
            ],
            'server' => [
                'server' => $_SERVER,
                'ip' => $request->ip(),
                'userAgent' => $request->userAgent()
            ]
        ];
    }

    public function reportSlack($data)
    {
        if (app()->environment('local') || app()->environment('testing')) {
            return;
        }
        $payload = [
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*Error:* ' . $data['errorInfo']['message'] . ' in ' . $data['errorInfo']['file'] . ' at line ' . $data['errorInfo']['line'] . ' on ' . date('Y-m-d H:i:s')
                    ]
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '*URL:* ' . $data['requestInfo']['url']
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Method:* ' . $data['requestInfo']['method']
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Input:* ' . json_encode($data['requestInfo']['input'])
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*User:* ' . json_encode($data['user'])
                        ],

                    ]
                ]
            ]
        ];

        $data = json_encode($payload);
        $ch = curl_init('https://hooks.slack.com/services/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');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json'
            )
        );
        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        if ($curlError) {
            Log::error('Curl Error: ' . $curlError);
        }
        curl_close($ch);
        return $result;
    }
}
