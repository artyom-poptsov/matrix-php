<?php

declare(strict_types = 1);

namespace matrix;

require_once(dirname(__FILE__) . "/common.php");
require_once(dirname(__FILE__) . "/Admin_session.php");

class Repository {
    protected Session $session;
    public function __construct(Session $session) {
        if ($session->get_access_token() == NULL) {
            throw new Matrix_exception("Session is not connected.");
        }
        $this->session = $session;
    }

    public function upload(string $file_path,
                           string $content_type) : void {
        $matrix_client = $this->session->get_matrix_client();
        $access_token  = $this->session->get_access_token();

        $json = $this->matrix_client->post_file(
            MATRIX_MEDIA_UPLOAD_URL,
            $file_path,
            [ 'access_token' => $this->access_token ],
            [ 'Content-Type' => $content_type       ]
        );
    }
}
