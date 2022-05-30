Dynamic Form Factory Project
=============================

Generates symfony forms based on YAML configurations files 

Getting Started
---------------
This project sample use Symfony 5.4

Usage
-----
The dynamic_form.yaml is available in ``config/packages/dynamic_form.yaml``

The Yaml structure for the Form should follow the next structure 

```
+---dynamic_form
|   \--- Form Name
|       \---Field Name
|           \---Field Type
|           \---Field Options
|           \---Field Transformer
|           \---Field Validators
|           \---Field DataProvider
```

Example
-------

Here's an example of a form named `user_form` with a single field called `email`:

```
dynamic_form:
  user_form:
    email:
      enabled: true
      type: 'Symfony\Component\Form\Extension\Core\Type\EmailType'
```


In controller 

```
use App\Factory\DynamicFormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var DynamicFormFactory
     */
    private $dynamicFormFactory;

    public function __construct(DynamicFormFactory $dynamicFormFactory)
    {
        $this->dynamicFormFactory = $dynamicFormFactory;
    }

    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {
         $form = $this->dynamicFormFactory->createForm('user_form');

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }
}

```

Options
-------
Field options are the same as symfony, referer to the documentation

Transformer:
------------
When using transformers write both the class where it is defined and the calls you need.

```
dynamic_form:
    user_form:
        email:
            enabled: true
            type: 'Symfony\Component\Form\Extension\Core\Type\EmailType'
            transformer:
              class: 'YourTransformer'
              calls:
                  - [yourMethodName, [your_params]]
                  - [yourMethodName, [your_params]]
```

Validators:
-----------
When Using validators call each validator constraint and its parameters like show donw below

```
dynamic_form:
    user_form:
        email:
            enabled: true
            type: 'Symfony\Component\Form\Extension\Core\Type\EmailType'
            validation:
              'Symfony\Component\Validator\Constraints\True':
                  message: 'your message'
              'Symfony\Component\Validator\Constraints\Length':
                  min: 2
                  max: 50
```

