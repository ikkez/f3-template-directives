<?php

class FormTest extends \Prefab {

	protected $repoPath;

	function __construct($repoPath='sugar/TemplateDirectives/') {
		$this->repoPath = $repoPath;
	}

	static public function init() {
		/** @var \Base $f3 */
		$f3 = \Base::instance();
		$f3->route('GET|POST /formtest','\FormTest->run');
		$f3->menu['/formtest'] = 'TemplateForms';
	}

	function run(\Base $f3) {

		\Template\Tags\Form::initAll();
		\Template\Tags\Markdown::init('markdown');

		$f3->set('fruits', array(
			'apple',
			'banana',
			'peach',
		));

		$f3->set('colors', array(
			'#f00'=>'red',
			'#0f0'=>'green',
			'#00f'=>'blue',
		));

		$f3->set('days', array(
			'mo'=>'monday',
			'tu'=>'tuesday',
			'we'=>'wednesday',
			'th'=>'thursday',
			'fr'=>'friday',
			'sa'=>'saturday',
			'su'=>'sunday'
		));
		$f3->UI = $this->repoPath.'ui/';

		// change source key
//		$f3->copy('POST','form1');
//		\Template\Tags\Form::instance()->setSrcKey('form1');

//		$f3->copy('POST','FORM.contact');
//		\Template\Tags\Form::instance()->setDynamicSrcKey(true);

		echo \Template::instance()->render('templates/formtest.html');

	}

} 