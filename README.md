Description
----
Welcome to the claim service! This component provides a convenience style api for creating and validating claims based on the ictu discpli project.

The service will first receive the data set linked to the provided person Uri. This has to be either an brp uri or haal centraal uri.

Based on the type specified in the post the claim service will then fill the claim with the required data from the person object. And then provides the claim as a JSON object and as a JWT token.

It also generates a QR code in base64 format containing the claim.

Installation
----

For deployment to clusters we use Helm 3.

For an in depth installation guide you can refer to the [installation guide](INSTALLATION.md).

##Tutorial

An in depth tutorial can be found [here](TUTORIAL.md).

####Setup your local environment
Before we can spin up our component we must first get a local copy from our repository, we can either do this through the command line or use a Git client. 

For this example we're going to use [GitKraken](https://www.gitkraken.com/) but you can use any tool you like, feel free to skip this part if you are already familiar with setting up a local clone of your repository.

Open gitkraken press "clone a repo" and fill in the form (select where on your local machine you want the repository to be stored, and fill in the link of your repository on github), press "clone a repo" and you should then see GitKraken downloading your code. After it's done press "open now" (in the box on top) and voilá your codebase (you should see an initial commit on a master branch).

You can now navigate to the folder where you just installed your code, it should contain some folders and files and generally look like this. We will get into the files later, lets first spin up our component!

Next make sure you have [docker desktop](https://www.docker.com/products/docker-desktop) running on your computer.

Open a command window (example) and browse to the folder where you just stuffed your code, navigating in a command window is done by cd, so for our example we could type 
cd c:\repos\common-ground\my-component (if you installed your code on a different disk then where the cmd window opens first type <diskname>: for example D: and hit enter to go to that disk, D in this case). We are now in our folder, so let's go! Type docker-compose up and hit enter. From now on whenever we describe a command line command we will document it as follows (the $ isn't actually typed but represents your folder structure):

```CLI
$ docker-compose up
```

Your computer should now start up your local development environment. Don't worry about al the code coming by, let's just wait until it finishes. You're free to watch along and see what exactly docker is doing, you will know when it's finished when it tells you that it is ready to handle connections. 

Open your browser type [<http://localhost/>](https://localhost) as address and hit enter, you should now see your common ground component up and running.


####Addition Calls

- info over afwijkende call


Additional Information
----

- [Contributing](CONTRIBUTING.md)

- [ChangeLogs](CHANGELOG.md)

- [RoadMap](ROADMAP.md)

- [Security](SECURITY.md)

- [Licence](LICENSE.md)

Credits
----

- waarop is datamodel gebaseerd
- eigenaar van code
