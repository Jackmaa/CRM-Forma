<?php
// src/Message/SendDocumentEmail.php
namespace App\Message;
final class SendDocumentEmail {
    public function __construct(public int $documentId, public string $to) {}
}
