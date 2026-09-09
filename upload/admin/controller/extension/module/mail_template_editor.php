<?php
class ControllerExtensionModuleMailTemplateEditor extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/mail_template_editor');
        $this->document->setTitle($this->language->get('heading_title'));

        // Use the bundled OpenCart/LiveStore Summernote as the default editor.
        // The template itself checks whether another editor has already claimed the textarea.
        $this->document->addStyle('view/javascript/summernote/summernote.css');
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');

        if (!$this->user->hasPermission('access', 'extension/module/mail_template_editor')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }

        $data['error_warning'] = '';
        $data['success'] = '';

        $language_files = $this->getLanguageFiles();
        $template_files = $this->getTemplateFiles();

        $kind = isset($this->request->get['kind']) ? $this->request->get['kind'] : 'language';
        $id = isset($this->request->get['id']) ? $this->request->get['id'] : '';

        if ($kind !== 'language' && $kind !== 'template') {
            $kind = 'language';
        }

        $files = ($kind === 'language') ? $language_files : $template_files;

        if (!$id || !isset($files[$id])) {
            $keys = array_keys($files);
            $id = $keys ? $keys[0] : '';
        }

        $selected = ($id && isset($files[$id])) ? $files[$id] : null;

        if ($selected && $kind === 'template') {
            $selected['mail_type'] = $this->detectMailType($selected['filename']);
            $files[$id]['mail_type'] = $selected['mail_type'];
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $selected) {
            if (!$this->user->hasPermission('modify', 'extension/module/mail_template_editor')) {
                $data['error_warning'] = $this->language->get('error_permission');
            } else {
                try {
                    if ($kind === 'language') {
                        $values = isset($this->request->post['values']) && is_array($this->request->post['values']) ? $this->request->post['values'] : array();
                        $this->validateLanguagePlaceholders($selected['path'], $values);
                        $this->backupFile($selected['path']);
                        $this->saveLanguageFile($selected['path'], $values);
                    } else {
                        $content = isset($this->request->post['content']) ? $this->request->post['content'] : '';
                        $this->validateTwigPlaceholders($selected['path'], $content);
                        $this->backupFile($selected['path']);
                        $this->saveTemplateFile($selected['path'], $content);
                    }

                    $data['success'] = $this->language->get('text_success');
                } catch (Exception $e) {
                    $data['error_warning'] = $e->getMessage();
                }
            }
        }

        $data['kind'] = $kind;
        $data['selected_id'] = $id;
        $data['selected'] = $selected;
        $data['language_files'] = $this->makeLinks($language_files, 'language');
        $data['template_files'] = $this->makeLinks($template_files, 'template');
        $data['values'] = array();
        $data['value_placeholders'] = array();
        $data['content'] = '';
        $data['template_placeholders'] = array();

        if ($selected) {
            if ($kind === 'language') {
                $data['values'] = $this->readLanguageFile($selected['path']);
                foreach ($data['values'] as $key => $value) {
                    $data['value_placeholders'][$key] = $this->extractPrintfPlaceholders($value);
                }
            } else {
                $data['content'] = file_get_contents($selected['path']);
                $data['template_placeholders'] = $this->extractTwigPlaceholders($data['content']);
            }
        }

        $data['action'] = $this->url->link('extension/module/mail_template_editor', 'user_token=' . $this->session->data['user_token'] . '&kind=' . $kind . '&id=' . urlencode($id), true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/mail_template_editor', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/mail_template_editor', $data));
    }

    public function install() {
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/mail_template_editor');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/mail_template_editor');
    }

    private function getFriendlyNames() {
        return array(
            'affiliate.php' => 'Партнёрская программа',
            'forgotten.php' => 'Восстановление пароля',
            'order_add.php' => 'Новый заказ — покупателю',
            'order_alert.php' => 'Новый заказ — администратору',
            'order_edit.php' => 'Изменение статуса заказа',
            'register.php' => 'Регистрация покупателя',
            'review.php' => 'Новый отзыв — администратору',
            'transaction.php' => 'Транзакция / изменение баланса',
            'voucher.php' => 'Подарочный сертификат',
            'affiliate.twig' => 'Партнёрская программа',
            'affiliate_alert.twig' => 'Партнёрская программа — администратору',
            'forgotten.twig' => 'Восстановление пароля',
            'order_add.twig' => 'Новый заказ — покупателю',
            'order_alert.twig' => 'Новый заказ — администратору',
            'order_edit.twig' => 'Изменение статуса заказа',
            'register.twig' => 'Регистрация покупателя',
            'register_alert.twig' => 'Регистрация — администратору',
            'transaction.twig' => 'Транзакция / изменение баланса',
            'voucher.twig' => 'Подарочный сертификат'
        );
    }

    private function friendlyName($file) {
        $names = $this->getFriendlyNames();
        return isset($names[$file]) ? $names[$file] : pathinfo($file, PATHINFO_FILENAME);
    }

    private function getLanguageFiles() {
        $result = array();
        $this->load->model('localisation/language');

        foreach ($this->model_localisation_language->getLanguages() as $language) {
            $code = basename($language['code']);
            $directory = DIR_CATALOG . 'language/' . $code . '/mail/';

            if (!is_dir($directory)) {
                continue;
            }

            foreach (glob($directory . '*.php') as $path) {
                $file = basename($path);
                $id = $code . ':' . $file;
                $result[$id] = array(
                    'id' => $id,
                    'label' => $this->friendlyName($file) . ' — ' . $language['name'],
                    'filename' => $file,
                    'path' => $path
                );
            }
        }

        uasort($result, function($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });
        return $result;
    }

    private function getTemplateFiles() {
        $result = array();
        $base = DIR_CATALOG . 'view/theme/';

        foreach (glob($base . '*/template/mail/*.twig') as $path) {
            $relative = substr($path, strlen($base));
            $parts = explode('/', $relative);

            if (count($parts) < 4) {
                continue;
            }

            $theme = basename($parts[0]);
            $file = basename($path);
            $id = $theme . ':' . $file;
            $result[$id] = array(
                'id' => $id,
                'label' => $this->friendlyName($file) . ' — тема ' . $theme,
                'filename' => $file,
                'path' => $path,
                'mail_type' => $this->detectMailType($file)
            );
        }

        uasort($result, function($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });
        return $result;
    }

    private function detectMailType($template_file) {
        static $map = null;

        if ($map === null) {
            $map = array();
            $controller_root = DIR_CATALOG . 'controller/';

            if (is_dir($controller_root)) {
                try {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($controller_root, FilesystemIterator::SKIP_DOTS)
                    );

                    foreach ($iterator as $file_info) {
                        if (!$file_info->isFile() || strtolower($file_info->getExtension()) !== 'php') {
                            continue;
                        }

                        $source = @file_get_contents($file_info->getPathname());

                        if ($source === false || strpos($source, "mail/") === false) {
                            continue;
                        }

                        preg_match_all(
                            '/->set(Html|Text)\s*\(\s*\$this->load->view\s*\(\s*[\'\"]mail\/([a-zA-Z0-9_\/.-]+)[\'\"]/i',
                            $source,
                            $matches,
                            PREG_SET_ORDER
                        );

                        foreach ($matches as $match) {
                            $route = basename($match[2]);
                            $key = $route . '.twig';
                            $type = strtolower($match[1]) === 'html' ? 'html' : 'text';

                            // HTML wins if the same template is used in both modes.
                            if (!isset($map[$key]) || $type === 'html') {
                                $map[$key] = $type;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Leave type unknown; the UI will fall back to the safe source editor.
                }
            }
        }

        return isset($map[$template_file]) ? $map[$template_file] : 'unknown';
    }

    private function makeLinks($files, $kind) {
        foreach ($files as &$file) {
            $file['href'] = $this->url->link('extension/module/mail_template_editor', 'user_token=' . $this->session->data['user_token'] . '&kind=' . $kind . '&id=' . urlencode($file['id']), true);
        }
        unset($file);
        return $files;
    }

    private function readLanguageFile($path) {
        if (!is_file($path)) {
            return array();
        }

        $_ = array();
        include($path);
        ksort($_);
        return $_;
    }

    private function extractPrintfPlaceholders($text) {
        preg_match_all('/%(?:\\d+\\$)?(?:[+\\-0 #]*)(?:\\d+|\\*)?(?:\\.\\d+|\\.\\*)?[bcdeEfFgGosuxX]/', (string)$text, $matches);
        return array_values(array_unique($matches[0]));
    }

    private function extractTwigPlaceholders($content) {
        preg_match_all('/{{\\s*[^{}]+?\\s*}}/', (string)$content, $matches);
        $tokens = array_map('trim', $matches[0]);
        return array_values(array_unique($tokens));
    }

    private function validateLanguagePlaceholders($path, $values) {
        $existing = $this->readLanguageFile($path);

        foreach ($existing as $key => $oldValue) {
            $newValue = array_key_exists($key, $values) ? (string)$values[$key] : (string)$oldValue;
            $required = $this->extractPrintfPlaceholders($oldValue);
            $present = $this->extractPrintfPlaceholders($newValue);
            $missing = array_diff($required, $present);

            if ($missing) {
                throw new Exception(sprintf($this->language->get('error_placeholder'), $key, implode(', ', $missing)));
            }
        }
    }

    private function validateTwigPlaceholders($path, $content) {
        $oldContent = is_file($path) ? file_get_contents($path) : '';
        $required = $this->extractTwigPlaceholders($oldContent);
        $present = $this->extractTwigPlaceholders($content);
        $missing = array_diff($required, $present);

        if ($missing) {
            throw new Exception(sprintf($this->language->get('error_twig_placeholder'), implode(', ', $missing)));
        }
    }

    private function saveLanguageFile($path, $values) {
        $existing = $this->readLanguageFile($path);
        $output = "<?php\n";

        foreach ($existing as $key => $old_value) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                continue;
            }

            $value = array_key_exists($key, $values) ? $values[$key] : $old_value;
            $output .= "$" . "_['" . $key . "'] = " . var_export((string)$value, true) . ";\n";
        }

        if (file_put_contents($path, $output, LOCK_EX) === false) {
            throw new Exception(sprintf($this->language->get('error_write'), $path));
        }
    }

    private function saveTemplateFile($path, $content) {
        if (file_put_contents($path, (string)$content, LOCK_EX) === false) {
            throw new Exception(sprintf($this->language->get('error_write'), $path));
        }
    }

    private function backupFile($path) {
        $backup_dir = DIR_STORAGE . 'mail_template_editor/';

        if (!is_dir($backup_dir) && !mkdir($backup_dir, 0755, true)) {
            throw new Exception(sprintf($this->language->get('error_backup'), $backup_dir));
        }

        $backup = $backup_dir . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]+/', '_', basename(dirname($path)) . '_' . basename($path)) . '.bak';

        if (!copy($path, $backup)) {
            throw new Exception(sprintf($this->language->get('error_backup'), $backup));
        }
    }
}
