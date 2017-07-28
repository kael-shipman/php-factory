# Factory

A class that provides overridable instantiation of classes based on text descriptors rather than concrete class names.

If you don't know about Dependency Injection, there are plenty of resources on the internet about it. Please make sure you understand what this is for before using it.

The short version is that you should find a way to allow yourself and other programmers to change what concrete implementation of the requested functionality is being used at any given time without having to change the code that uses it.

I've chosen to implement this by creating a `Factory` class that is passed on construct to all of my other classes. When one of my objects needs to create a child object, it tells the factory what it wants, giving it the parameters it wants to be passed into the constructor, and the Factory gives it an instance of some class that was defined by the programmer when the application was configured.

The program itself won't always know what actual class of object this is, and in fact shouldn't care. What it should care about is that whatever object it gets back implements the interface it's expecting.

## Usage

Here's a basic example of how this should be *used* (implementation is below):

```php

// Get your factory instance
$f = MyFactory::getInstance();

// Use it to create a new app instance
$app = $f->new('app');

// Since you can't guarantee *what* app instance it gave you, you have to verify that it implements the interface you're expecting
if (!($app instanceof AppIWannaRun)) throw new RuntimeException("App returned by factory must be of type `AppIWannaRun`");

// Now run it
$app->run();

```

This is an extremely high-level example, but it demonstrates that

1. you tell the factory what *type* of object you want (my implementation actually also allows for subtypes); and
2. you verify that the instance that you get back *implements the interface* you're expecting.

You'll usually find more complex examples of this further inside the code. Consider this:

```php

class MyApp {
    protected $factory;
    protected $db;

    // Here, you have to pass an instance of your factory to the app on construct, along with an instance of a DB
    public function __construct(FactoryInterface $f, DatabaseInterface $db) {
        $this->factory = $f;
        $this->db = $db;
    }

    // Do lots of stuff here ......

    // Now here's an internal method
    protected function getInternalThings() {
        // Get things from the DB
        $things = $this->db->query('SELECT * FROM `things`');

        // Create a thing collection
        $thingCollection = $this->factory->new('collection', 'things');

        // Iterate over the db results, adding things to the collection
        foreach($things as $thing) {
            $thingCollection[] = $this->factory->create('thing', null, $thing);
        }

        // Return the collection
        return $thingCollection;
    }

    // ...
}

```

In the above example, we used the factory to instantiate a collection, then used its special "create" method (which only works for classes that implement a static "create" method themselves) to "restore" instances of Things from data. This worked because somewhere, you told your factory what class represented a "collection of things", and what class represented a "thing".

You did that by....

### Creating Your Type Map

In the above examples, we requested an object of type `app`, an object of type `collection` with subtype `things`, and several objects of type `thing`. But the factory isn't born knowing how to create these objects. You have to tell it. And you do this by *subclassing* Factory and overriding the `getClass` method.

To create a factory that can instantiate all these objects, here's what we do:

```php
<?php
namespace MyNamespace;

class MyFactory extends \KaelShipman\Factory {
    // Signature has to match the original Factory::getClass method
    public function getClass(string $type, string $subtype=null) {
        // Return the class as a string including namespace
        if ($type == 'app') {
            // Always a good practice to check for subtype, even if you don't need one today
            if (!$subtype || $subtype == 'generic') return '\\MyNamespace\\App';
        }

        if ($type == 'collection') {
            if ($subtype == 'thing') return '\\MyNamespace\\ThingsCollection';
            // Notice, if we're asking for a subtype we don't know how to create, it falls through
        }

        if ($type == 'thing') {
            if (!$subtype || $subtype == 'generic') return '\\MyNamespace\\Thing';
        }

        // Fall through to parent
        return parent::getClass($type, $subtype);
    }
}
```

Sometimes you'll put this in a separate file. More often, though, this can go right in your front controller (usually `index.php` in a web app).

If you're writing something that someone may extend, it's a good idea to put your custom factory in its own class file. That way, someone else can extend it and avoid having to remap all the objects you've already mapped.

## Conclusion

That's it! Hope it's useful to someone.

