-- phpMyAdmin SQL Dump
-- version 4.6.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 10 2016 г., 18:38
-- Версия сервера: 5.5.36-34.0-632.precise
-- Версия PHP: 5.6.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cb92348_cms`
--

-- --------------------------------------------------------

--
-- Структура таблицы `entity`
--

CREATE TABLE IF NOT EXISTS `entity` (
  `entity_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `creator_id` int(11) NOT NULL,
  `params` text NOT NULL,
  `entity_group_id` int(11) NOT NULL,
  `use_sections` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `idx_1` (`title`,`creator_id`,`entity_group_id`),
  KEY `entity_group_id` (`entity_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `entity`
--

INSERT INTO `entity` (`entity_id`, `title`, `date_add`, `date_update`, `creator_id`, `params`, `entity_group_id`, `use_sections`, `priority`) VALUES
(2, 'Статьи', '0000-00-00 00:00:00', '2015-12-19 22:45:27', 0, '{"signatures":{"1":{"title":"Разделы"},"2":{"title":"Раздел"},"3":{"title":"Добавить раздел"},"4":{"title":"Изменить раздел"},"5":{"title":"Удалить раздел"},"6":{"title":"Элементы"},"7":{"title":"Элемент"},"8":{"title":"Добавить элемент"},"9":{"title":"Изменить элемент"},"10":{"title":"Удалить элемент"}}}', 1, 1, 0),
(6, 'Новости', '0000-00-00 00:00:00', '2015-10-04 13:06:22', 0, '{"signatures":{"1":{"title":"Разделы"},"2":{"title":"Раздел"},"3":{"title":"Добавить раздел"},"4":{"title":"Изменить раздел"},"5":{"title":"Удалить раздел"},"6":{"title":"Элементы"},"7":{"title":"Элемент"},"8":{"title":"Добавить элемент"},"9":{"title":"Изменить элемент"},"10":{"title":"Удалить элемент"}}}', 2, 1, 10);

-- --------------------------------------------------------

--
-- Структура таблицы `entity_access`
--

CREATE TABLE IF NOT EXISTS `entity_access` (
  `entity_access_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  `access` int(11) NOT NULL,
  PRIMARY KEY (`entity_access_id`),
  KEY `entity_id` (`entity_id`,`user_group_id`),
  KEY `user_group_id` (`user_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `entity_access`
--

INSERT INTO `entity_access` (`entity_access_id`, `entity_id`, `user_group_id`, `access`) VALUES
(12, 2, 1, 16),
(13, 2, 2, 4),
(20, 6, 1, 0),
(21, 6, 2, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `entity_admin_display`
--

CREATE TABLE IF NOT EXISTS `entity_admin_display` (
  `entity_admin_display_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) NOT NULL,
  `entity_section_id` int(11) DEFAULT '0',
  `list_data` mediumtext,
  `detail_data` mediumtext,
  `user_id` int(11) DEFAULT '0',
  `relation` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 - итем, 2 - раздел',
  PRIMARY KEY (`entity_admin_display_id`),
  KEY `entity_id` (`entity_id`,`entity_section_id`,`user_id`,`relation`),
  KEY `entity_section_id` (`entity_section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `entity_admin_display`
--

INSERT INTO `entity_admin_display` (`entity_admin_display_id`, `entity_id`, `entity_section_id`, `list_data`, `detail_data`, `user_id`, `relation`) VALUES
(50, 2, 0, '[{"isBase":"1","field":"title"},{"isBase":"0","field":"16"},{"isBase":"1","field":"sections"}]', '[{"title":"Общие","items":[{"type":"field","isBase":"1","field":"title"},{"type":"field","isBase":"1","field":"active"},{"type":"field","isBase":"1","field":"description"},{"type":"field","isBase":"1","field":"sections"},{"type":"field","isBase":"0","field":"1"},{"type":"field","isBase":"0","field":"13"},{"type":"field","isBase":"0","field":"15"},{"type":"field","isBase":"0","field":"16"}]}]', 0, 1),
(51, 2, 0, '[{"isBase":"1","field":"title"},{"isBase":"1","field":"active"}]', '[{"title":"Общие","items":[{"type":"field","isBase":"1","field":"title"},{"type":"field","isBase":"1","field":"active"},{"type":"field","isBase":"1","field":"description"},{"type":"field","isBase":"1","field":"sections"},{"type":"field","isBase":"0","field":"1"},{"type":"field","isBase":"0","field":"13"},{"type":"field","isBase":"0","field":"15"},{"type":"field","isBase":"0","field":"16"}]}]', 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `entity_field`
--

CREATE TABLE IF NOT EXISTS `entity_field` (
  `entity_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) NOT NULL,
  `relation` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 - элемент, 2 - раздел',
  `title` varchar(255) NOT NULL,
  `description` text,
  `params` text NOT NULL,
  `type` int(11) NOT NULL COMMENT 'вариант поля (текст, строка, и прочее)',
  `priority` int(11) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_unique` tinyint(1) NOT NULL DEFAULT '0',
  `is_multi` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  PRIMARY KEY (`entity_field_id`),
  KEY `idx_1` (`entity_id`,`priority`,`type`,`is_required`,`is_unique`,`is_multi`,`relation`,`title`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `entity_field`
--

INSERT INTO `entity_field` (`entity_field_id`, `entity_id`, `relation`, `title`, `description`, `params`, `type`, `priority`, `is_required`, `is_unique`, `is_multi`, `date_add`, `date_update`) VALUES
(1, 2, 2, 'Размер', '\r\n', '{"some":""}', 2, 1, 1, 1, 0, '0000-00-00 00:00:00', '2015-12-19 22:25:28'),
(13, 2, 1, 'Цена6', NULL, '{"some":""}', 1, 10, 0, 0, 1, '0000-00-00 00:00:00', '2015-12-19 22:24:15'),
(15, 2, 1, 'Список', NULL, '{"view":"checkbox"}', 3, 10, 0, 0, 1, '2015-10-06 16:01:50', '2015-10-08 13:18:05'),
(16, 2, 1, 'Статус', NULL, '{"view":"radio"}', 3, 10, 1, 0, 0, '2015-10-07 14:38:34', '2015-10-07 14:38:34'),
(17, 2, 2, '567657567', NULL, '{"some":""}', 1, 10, 0, 0, 0, '2015-11-15 18:47:33', '2015-11-15 18:47:38');

-- --------------------------------------------------------

--
-- Структура таблицы `entity_field_variant`
--

CREATE TABLE IF NOT EXISTS `entity_field_variant` (
  `entity_field_variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `entity_field_id` int(11) NOT NULL,
  PRIMARY KEY (`entity_field_variant_id`),
  KEY `title` (`title`(255),`priority`,`entity_field_id`),
  KEY `entity_field_id` (`entity_field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `entity_field_variant`
--

INSERT INTO `entity_field_variant` (`entity_field_variant_id`, `title`, `priority`, `entity_field_id`) VALUES
(16, 'Открыт', 0, 16),
(17, 'В работе', 1, 16),
(18, 'Выполнен', 2, 16),
(19, 'Завершен', 3, 16),
(24, '56', 0, 15),
(26, '7878', 1, 15);

-- --------------------------------------------------------

--
-- Структура таблицы `entity_group`
--

CREATE TABLE IF NOT EXISTS `entity_group` (
  `entity_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` mediumtext,
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  PRIMARY KEY (`entity_group_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `entity_group`
--

INSERT INTO `entity_group` (`entity_group_id`, `title`, `description`, `date_add`, `date_update`) VALUES
(1, 'Инфоблоки', '345\r\n', '2015-10-04 12:01:00', '2015-10-04 12:03:22'),
(2, 'Прочее', NULL, '2015-10-04 13:04:40', '2015-10-04 13:04:40'),
(3, '567567', NULL, '2015-10-06 12:14:04', '2015-10-06 12:14:04');

-- --------------------------------------------------------

--
-- Структура таблицы `file_storage`
--

CREATE TABLE IF NOT EXISTS `file_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `size` bigint(20) NOT NULL,
  `date_add` datetime NOT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `file_storage`
--

INSERT INTO `file_storage` (`id`, `name`, `original_name`, `description`, `path`, `type`, `size`, `date_add`, `params`) VALUES
(1, 'some.jpg', '', '', '', '', 0, '0000-00-00 00:00:00', NULL),
(2, '76a99f34d858bbc01f7c327814ff3099.php', 't.php', NULL, '0e5/2e4', NULL, 1479, '2016-02-22 18:16:43', NULL),
(3, 'c4aa211cddd7f26ed1e8851c25f0ce55.php', 't.php', NULL, 'c9e/1e9', NULL, 1479, '2016-02-22 18:19:34', NULL),
(4, 'a3628ac5ed80eba610c1ef242459de3b.php', 't.php', NULL, 'b1e/63b', NULL, 1479, '2016-02-22 18:19:35', NULL),
(5, 'a0a2560dfc6776ffa36e3f60f184de73.php', 't.php', NULL, 'e51/ca8', NULL, 1479, '2016-02-22 18:19:36', NULL),
(6, '5d51c2038e03c139f900259c0ebdf49c.php', 't.php', NULL, '53f/931', NULL, 1479, '2016-02-22 18:19:40', NULL),
(7, '9ea9028558200feb42e2f04b7f21ab91.php', 't.php', NULL, '484/33a', NULL, 1479, '2016-02-22 18:20:12', NULL),
(8, 'ea7c3445629dfc9589787da2427e1cf2.php', 't.php', NULL, '398/20a', NULL, 1479, '2016-02-22 18:20:13', NULL),
(9, 'dcb01fb48abe86cce039d0f824ccaca1.php', 't.php', NULL, '28b/3c0', NULL, 1479, '2016-02-22 18:20:30', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `module_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` mediumtext,
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `module`
--

INSERT INTO `module` (`module_id`, `title`, `alias`, `description`) VALUES
(1, 'Сущности', 'entities', 'Сущности'),
(2, 'Ядро', 'core', 'Ядро');

-- --------------------------------------------------------

--
-- Структура таблицы `new_entity_access`
--

CREATE TABLE IF NOT EXISTS `new_entity_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` varchar(255) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  `access` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `new_entity_access`
--

INSERT INTO `new_entity_access` (`id`, `entity_id`, `user_group_id`, `access`) VALUES
(1, 'user', 2, 16),
(9, 'user', 2, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `new_entity_display`
--

CREATE TABLE IF NOT EXISTS `new_entity_display` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` varchar(255) NOT NULL,
  `data` mediumtext,
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `new_entity_display`
--

INSERT INTO `new_entity_display` (`id`, `entity_id`, `data`, `user_id`) VALUES
(14, 'user', '{"list":[{"field":"id"},{"field":"login"},{"field":"groups"},{"field":"active"}],"detail":[{"title":"Основное","fields":{"1":{"field":"id"},"0":{"field":"active"},"2":{"field":"login"},"3":{"field":"password"},"4":{"field":"fio"},"5":{"field":"email"},"6":{"field":"gender"}}},{"title":"Группа","fields":{"7":{"field":"groups"}}}],"filter":[{"field":"id"},{"field":"login"},{"field":"active"},{"field":"groups"}]}', 0),
(15, 'user_group', '{"detail":[{"title":"Основное","fields":[{"field":"id"},{"field":"title"},{"field":"alias"}]},{"title":"Доступ","fields":{"1":{"field":"access"}}}],"filter":[{"field":"title"},{"field":"access"},{"field":"date_add"},{"field":"date_update"}],"list":[{"field":"id"},{"field":"title"},{"field":"access"}]}', 0),
(16, 'user_group', '{"detail":[{"title":"Основное","fields":[{"field":"id"},{"field":"title"},{"field":"alias"}]},{"title":"Доступ","fields":{"5":{"field":"access"}}}]}', 1),
(17, 'user', '{"filter":[{"field":"login"},{"field":"fio"},{"field":"id"},{"field":"active"},{"field":"group"}],"list":[{"field":"id"},{"field":"fio"},{"field":"login"},{"field":"gender"},{"field":"active"},{"field":"some"},{"field":"group"},{"field":"date_update"},{"field":"date_add"}],"detail":[{"title":"Основное","fields":[{"field":"id"},{"field":"active"},{"field":"login"},{"field":"password"},{"field":"fio"},{"field":"email"},{"field":"gender"}]},{"title":"Группа","fields":{"7":{"field":"date_add"},"8":{"field":"date_update"},"9":{"field":"some"},"10":{"field":"group"}}}]}', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `new_entity_extra_field`
--

CREATE TABLE IF NOT EXISTS `new_entity_extra_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `relation` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 - элемент, 2 - раздел',
  `title` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `description` text,
  `params` text,
  `type` varchar(255) NOT NULL COMMENT 'вариант поля (текст, строка, и прочее)',
  `priority` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `is_unique` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `multi` tinyint(1) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_1` (`entity_id`,`priority`,`type`,`required`,`is_unique`,`multi`,`relation`,`title`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `new_entity_extra_field`
--

INSERT INTO `new_entity_extra_field` (`id`, `entity_id`, `alias`, `relation`, `title`, `caption`, `description`, `params`, `type`, `priority`, `required`, `is_unique`, `visible`, `multi`, `date_add`, `date_update`) VALUES
(43, 'user', 'some', 1, 'Тестовое поле', '', '', '', '\\Entity\\Field\\Additional\\StringField', 0, 0, 1, 1, 1, '2015-12-30 13:50:12', '2016-04-11 10:05:42');

-- --------------------------------------------------------

--
-- Структура таблицы `new_entity_extra_field_access`
--

CREATE TABLE IF NOT EXISTS `new_entity_extra_field_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extra_field_id` int(11) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  `access` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `extra_field_id` (`extra_field_id`,`user_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `new_entity_extra_field_value`
--

CREATE TABLE IF NOT EXISTS `new_entity_extra_field_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `entity_id` varchar(255) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value_text` mediumtext,
  `value_string` varchar(255) DEFAULT NULL,
  `value_num` double DEFAULT '0',
  `value_enum` int(11) DEFAULT NULL,
  `value_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_string` (`item_id`,`field_id`,`value_string`),
  KEY `idx_num` (`item_id`,`field_id`,`value_num`),
  KEY `idx_enum` (`item_id`,`field_id`,`value_enum`),
  KEY `idx_date` (`item_id`,`field_id`,`value_date`),
  KEY `item_id` (`field_id`,`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `new_entity_extra_field_value`
--

INSERT INTO `new_entity_extra_field_value` (`id`, `item_id`, `entity_id`, `field_id`, `value_text`, `value_string`, `value_num`, `value_enum`, `value_date`) VALUES
(14, 1, 'user', 43, NULL, 'ss', 0, NULL, NULL),
(15, 1, 'user', 43, NULL, 'df', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `new_entity_extra_field_variant`
--

CREATE TABLE IF NOT EXISTS `new_entity_extra_field_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `extra_field_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `extra_field_id` (`extra_field_id`,`title`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `new_entity_extra_field_variant`
--

INSERT INTO `new_entity_extra_field_variant` (`id`, `title`, `caption`, `priority`, `date_add`, `date_update`, `extra_field_id`) VALUES
(6, 'Some variant 1', '', 0, '2016-01-05 18:49:27', '2016-01-05 18:49:27', 43),
(7, 'Some variant 1', '', 0, '2016-01-05 18:49:28', '2016-01-05 18:49:28', 43),
(8, 'Some variant 1', '', 0, '2016-01-05 18:49:28', '2016-01-05 18:49:28', 43),
(9, 'Some variant 1', '', 0, '2016-01-05 18:52:04', '2016-01-05 18:52:04', 43),
(10, 'Some variant 1', '', 0, '2016-01-05 18:52:05', '2016-01-05 18:52:05', 43),
(11, 'Some variant 1', '', 0, '2016-01-05 18:52:05', '2016-01-05 18:52:05', 43),
(12, 'Some variant 1', '', 0, '2016-01-05 18:52:06', '2016-01-05 18:52:06', 43),
(13, 'Some variant 1', '', 0, '2016-01-05 18:52:06', '2016-01-05 18:52:06', 43),
(14, 'Some variant 1', '', 0, '2016-01-05 18:52:06', '2016-01-05 18:52:06', 43);

-- --------------------------------------------------------

--
-- Структура таблицы `param`
--

CREATE TABLE IF NOT EXISTS `param` (
  `param_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` mediumtext,
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`param_id`),
  KEY `category` (`name`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `param`
--

INSERT INTO `param` (`param_id`, `name`, `value`, `user_id`) VALUES
(48, 'settings.upload.dir', '/upload', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `fio` varchar(255) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `gender` varchar(1) NOT NULL DEFAULT '0',
  `picture` int(11) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `gender` (`gender`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `login`, `password`, `fio`, `email`, `gender`, `picture`, `date_add`, `date_update`, `active`) VALUES
(1, 'newLogin887', '7c4a8d09ca3762af61e59520943dc26494f8941b', '', '', '0', NULL, '2016-02-21 21:04:21', '2016-06-04 18:53:19', 1),
(2, 'nikita12', '4406285f2336d66c4e74dadbd8ba3d645a6d1de1', 'Петухов Никита Евгеньевич', 'pirelli89@mail.ru', 'M', NULL, '2016-02-21 21:26:18', '2016-06-04 18:53:19', 0),
(3, 'testtestov', '69c5fcebaa65b560eaf06c3fbeb481ae44b8d618', '', 'pirelli89@mail.ru', '0', NULL, '2016-02-22 15:50:06', '2016-06-04 18:53:19', 1),
(4, 'svantest', '128332b62e6205a77578ad7d8fd35c40572c9022', 'Тестов Тест Тестович', '', '0', NULL, '2016-04-11 10:07:19', '2016-06-04 18:53:19', 1),
(5, 'nikita', '123456', '', '', '0', NULL, '2020-06-20 06:00:00', '2016-06-06 11:50:51', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `date_add` datetime DEFAULT NULL,
  `date_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_1` (`title`,`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_group`
--

INSERT INTO `user_group` (`id`, `title`, `alias`, `date_add`, `date_update`) VALUES
(1, 'Администраторы', 'ADMIN', NULL, NULL),
(2, 'Неавторизованные', 'UNAUTHORISED', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `user_group_access`
--

CREATE TABLE IF NOT EXISTS `user_group_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` mediumtext,
  PRIMARY KEY (`id`),
  KEY `title` (`title`,`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_group_access`
--

INSERT INTO `user_group_access` (`id`, `title`, `alias`, `description`) VALUES
(7, 'Доступ к сущностям', 'ENTITY_ACCESS', 'Доступ к модулю сущности. Просмотр, изменение, удаление'),
(9, 'Доступ к административной панели', 'ADMIN_ACCESS', 'Доступ к административной части');

-- --------------------------------------------------------

--
-- Структура таблицы `user_group_access_value`
--

CREATE TABLE IF NOT EXISTS `user_group_access_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `user_group_access_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_group_id` (`user_group_id`),
  KEY `user_group_access_id` (`user_group_access_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_group_access_value`
--

INSERT INTO `user_group_access_value` (`id`, `user_group_id`, `user_group_access_id`) VALUES
(1, 1, 9);

-- --------------------------------------------------------

--
-- Структура таблицы `user_group_value`
--

CREATE TABLE IF NOT EXISTS `user_group_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`user_group_id`),
  KEY `user_group_id` (`user_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_group_value`
--

INSERT INTO `user_group_value` (`id`, `user_id`, `user_group_id`) VALUES
(1, 1, 1),
(7, 1, 2),
(10, 1, 3),
(9, 1, 4),
(3, 2, 1),
(11, 2, 2),
(12, 2, 3),
(13, 2, 4),
(14, 3, 1),
(4, 3, 2),
(15, 3, 3),
(16, 3, 4),
(5, 4, 1),
(17, 4, 2),
(18, 4, 3),
(19, 4, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `user_permanent_auth`
--

CREATE TABLE IF NOT EXISTS `user_permanent_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `last_auth` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_permanent_auth`
--

INSERT INTO `user_permanent_auth` (`id`, `user_id`, `hash`, `last_auth`) VALUES
(26, 1, 'cd60cbcde9c6c1e35f2d701eac9941e2', '2016-05-17 14:49:37');

-- --------------------------------------------------------

--
-- Структура таблицы `user_session`
--

CREATE TABLE IF NOT EXISTS `user_session` (
  `session_id` varchar(32) NOT NULL,
  `expire` int(11) UNSIGNED NOT NULL,
  `remember` tinyint(1) NOT NULL DEFAULT '0',
  `data` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_session`
--

INSERT INTO `user_session` (`session_id`, `expire`, `remember`, `data`, `user_id`) VALUES
('3f531de4d75e680e16372e98a8cf183f', 1465502127, 0, 'user_session|a:12:{s:2:"id";s:1:"1";s:5:"login";s:11:"newLogin887";s:8:"password";s:40:"7c4a8d09ca3762af61e59520943dc26494f8941b";s:3:"fio";s:0:"";s:5:"email";s:0:"";s:6:"gender";s:1:"0";s:6:"active";s:1:"1";s:8:"date_add";s:19:"2016-02-21 21:04:21";s:11:"date_update";s:19:"2016-06-04 18:53:19";s:8:"group_id";a:2:{i:1;s:1:"1";i:2;s:1:"2";}s:4:"some";a:2:{i:14;s:2:"ss";i:15;s:2:"df";}s:6:"groups";a:1:{s:12:"UNAUTHORISED";a:5:{s:2:"id";s:1:"2";s:5:"title";s:32:"Неавторизованные";s:5:"alias";s:12:"UNAUTHORISED";s:8:"date_add";N;s:11:"date_update";N;}}}', 0),
('662fc2aed785d5585598e743900a0951', 1465587912, 0, 'user_session|a:12:{s:2:"id";s:1:"1";s:5:"login";s:11:"newLogin887";s:8:"password";s:40:"7c4a8d09ca3762af61e59520943dc26494f8941b";s:3:"fio";s:0:"";s:5:"email";s:0:"";s:6:"gender";s:1:"0";s:6:"active";s:1:"1";s:8:"date_add";s:19:"2016-02-21 21:04:21";s:11:"date_update";s:19:"2016-06-04 18:53:19";s:5:"group";a:4:{i:1;s:1:"1";i:7;s:1:"2";i:10;s:1:"3";i:9;s:1:"4";}s:4:"some";a:2:{i:14;s:2:"ss";i:15;s:2:"df";}s:6:"groups";a:1:{s:12:"UNAUTHORISED";a:5:{s:2:"id";s:1:"2";s:5:"title";s:32:"Неавторизованные";s:5:"alias";s:12:"UNAUTHORISED";s:8:"date_add";N;s:11:"date_update";N;}}}', 0),
('6c60e14fdb585bed7f358364247adb45', 1465655175, 0, 'user_session|a:12:{s:2:"id";s:1:"1";s:5:"login";s:11:"newLogin887";s:8:"password";s:40:"7c4a8d09ca3762af61e59520943dc26494f8941b";s:3:"fio";s:0:"";s:5:"email";s:0:"";s:6:"gender";s:1:"0";s:6:"active";s:1:"1";s:8:"date_add";s:19:"2016-02-21 21:04:21";s:11:"date_update";s:19:"2016-06-04 18:53:19";s:5:"group";a:4:{i:1;s:1:"1";i:7;s:1:"2";i:10;s:1:"3";i:9;s:1:"4";}s:4:"some";a:2:{i:14;s:2:"ss";i:15;s:2:"df";}s:6:"groups";a:1:{s:12:"UNAUTHORISED";a:5:{s:2:"id";s:1:"2";s:5:"title";s:32:"Неавторизованные";s:5:"alias";s:12:"UNAUTHORISED";s:8:"date_add";N;s:11:"date_update";N;}}}', 0);

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `entity`
--
ALTER TABLE `entity`
  ADD CONSTRAINT `entity_ibfk_1` FOREIGN KEY (`entity_group_id`) REFERENCES `entity_group` (`entity_group_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_access`
--
ALTER TABLE `entity_access`
  ADD CONSTRAINT `entity_access_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`entity_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_admin_display`
--
ALTER TABLE `entity_admin_display`
  ADD CONSTRAINT `entity_admin_display_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`entity_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Ограничения внешнего ключа таблицы `entity_field`
--
ALTER TABLE `entity_field`
  ADD CONSTRAINT `entity_field_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`entity_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_field_group--`
--
ALTER TABLE `entity_field_group--`
  ADD CONSTRAINT `entity_field_group--_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`entity_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_field_group_value--`
--
ALTER TABLE `entity_field_group_value--`
  ADD CONSTRAINT `entity_field_group_value--_ibfk_1` FOREIGN KEY (`entity_field_id`) REFERENCES `entity_field` (`entity_field_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entity_field_group_value--_ibfk_2` FOREIGN KEY (`entity_field_group_id`) REFERENCES `entity_field_group--` (`entity_field_group_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_field_variant`
--
ALTER TABLE `entity_field_variant`
  ADD CONSTRAINT `entity_field_variant_ibfk_1` FOREIGN KEY (`entity_field_id`) REFERENCES `entity_field` (`entity_field_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_item`
--
ALTER TABLE `entity_item`
  ADD CONSTRAINT `entity_item_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`entity_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_item_field_value`
--
ALTER TABLE `entity_item_field_value`
  ADD CONSTRAINT `entity_item_field_value_ibfk_1` FOREIGN KEY (`entity_item_id`) REFERENCES `entity_item` (`entity_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entity_item_field_value_ibfk_2` FOREIGN KEY (`entity_field_id`) REFERENCES `entity_field` (`entity_field_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_section_element`
--
ALTER TABLE `entity_section_element`
  ADD CONSTRAINT `entity_section_element_ibfk_4` FOREIGN KEY (`entity_element_id`) REFERENCES `entity_item` (`entity_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entity_section_element_ibfk_5` FOREIGN KEY (`entity_section_id`) REFERENCES `entity_section_tree` (`entity_item_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_section_tree`
--
ALTER TABLE `entity_section_tree`
  ADD CONSTRAINT `entity_section_tree_ibfk_1` FOREIGN KEY (`entity_item_id`) REFERENCES `entity_item` (`entity_item_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `entity_signature--`
--
ALTER TABLE `entity_signature--`
  ADD CONSTRAINT `entity_signature--_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`entity_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `new_entity_extra_field_access`
--
ALTER TABLE `new_entity_extra_field_access`
  ADD CONSTRAINT `new_entity_extra_field_access_ibfk_1` FOREIGN KEY (`extra_field_id`) REFERENCES `new_entity_extra_field` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `new_entity_extra_field_variant`
--
ALTER TABLE `new_entity_extra_field_variant`
  ADD CONSTRAINT `new_entity_extra_field_variant_ibfk_1` FOREIGN KEY (`extra_field_id`) REFERENCES `new_entity_extra_field` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
