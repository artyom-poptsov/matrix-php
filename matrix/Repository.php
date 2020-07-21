<?php

declare(strict_types = 1);

namespace matrix;

require_once(dirname(__FILE__) . "/common.php");
require_once(dirname(__FILE__) . "/Admin_session.php");
require_once(dirname(__FILE__) . "/Content_URI.php");

class Repository {
    protected Session $session;
    public function __construct(Session $session) {
        if ($session->get_access_token() == NULL) {
            throw new Matrix_exception("Session is not connected.");
        }
        $this->session = $session;
    }

    /**
     * Upload a file to the content repository.
     *
     * @param string $file_path A path to the local file.
     * @param string $content_type A type of the file contents.
     * @return Content_URI A content URI of the uploaded file.
     */
    public function upload(string $file_path,
                           string $content_type) : Content_URI {
        $matrix_client = $this->session->get_matrix_client();
        $access_token  = $this->session->get_access_token();

        $json = $matrix_client->post_file(
            MATRIX_MEDIA_UPLOAD_URL,
            $file_path,
            [ 'access_token' => $access_token ],
            [ 'Content-Type' => $content_type ]
        );
        return new Content_URI($json['content_uri']);
    }
}
