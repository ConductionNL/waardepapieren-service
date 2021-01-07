# Waardepapieren Service
## Additional Information



For deployment to kubernetes clusters we use Helm 3.

For an in depth installation guide you can refer to the [installation guide](INSTALLATION.md).

- [Contributing](CONTRIBUTING.md)

- [ChangeLogs](CHANGELOG.md)

- [RoadMap](ROADMAP.md)

- [Security](SECURITY.md)

- [License](LICENSE.md)

## Description


Welcome to the claim service! This component provides a convenience style api for creating and validating claims based on the ICTU discipl project.

The service will first receive the data set linked to the provided person Uri. This has to be either a brp uri or "Haal Centraal" uri.

Based on the type specified in the post the claim service will then fill the claim with the required data from the person object. 

It then provides the claim both as a JSON object and a JWT token.

It also generates a QR code in base64 format containing the claim.

![qr](resources/qr.png)

![pdf](resources/pdf.png)

### About Waardepapieren

The waardenpapieren project aims at digitizing proof from the dutch government for its citizens (e.g. birth certificates, marriage certificates and proof of residence and residential history) it is based on the [W3C claims structure](https://w3c.github.io/vc-data-model/#claims) for standardization.

At the core of the waardepapieren concept is that a “proof” should be applicable both digital and non-digital. Therefore a proof is presented as a PDF containing an JTW based claim, the claim itself however can also be used separately. For more information about the inner workings of waardepapieren see the waardepapieren service at it [repro]( https://github.com/ConductionNL/waardepapieren-service).

### Online test environment
There are several online environments available for testing

1. [Example user interface](https://waardepapieren-gemeentehoorn.commonground.nu)
2. [Example registration desk interface](https://waardepapieren-gemeentehoorn.commonground.nu/waardepapieren-balie)
3. [Example Wordpress implementation](https://dev.zuiddrecht.nl)
4. [Example Waardepapieren Service](https://waardepapieren-gemeentehoorn.commonground.nu/api/v1/waar)
5. [Example Waardepapieren Registration](https://waardepapieren-gemeentehoorn.commonground.nu/api/v1/wari )

### Dependencies

For this repository you will need an API key at a waardepapieren service, a valid api key can be obtained at [Dimpac](https://www.dimpact.nl/) a key for the test api can be obtained from [Conduction](https://condution.nl).

### Setup your local environment
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

### Installation 
This repository comes with an helm installation package and guide for installations on kubernates and haven environments. The installation guide can be found under INSTALLATION.md[](INSTALLATION.md).

### Using the api 
In order to create a waardepapier you nee to make a API call to the waarde papieren service, the preffered format for this is JSON. If you are running the applicaton locally the endpoint is `localhost`. The post should at least contain the BSN number of the person for who you are trieng to create a waardepapier, the type of the waardepapier to be created and the organization reqesting the waardepapier (for styling reasons) e.g.

```JSON
{
    "person":999999928"",
    "type": "akte_van_geboorte",
    "organization": "001516814"
}
```

You will then be be presented with a json(-ld)return object containing
1. an claim (jwt object)
2. an discipl representation of the claim
3. an irma representation of the claim
4. an [png image](/resources/akte_van_geboorte.pdf) of the claim
5. an [pdf representation](/resources/akte_van_geboorte.png) of the claim in the organization style

e.g.
```JSON
{
    "@context": "/contexts/Certificate",
    "@id": "/certificates/b8068ab1-6adf-4850-be92-c967dad6597d",
    "@type": "Certificate",
    "id": "b8068ab1-6adf-4850-be92-c967dad6597d",
    "person": "999999928",
    "type": "akte_van_geboorte",
    "organization": "001516814",
    "claim": {
        "iss": "b8068ab1-6adf-4850-be92-c967dad6597d",
        "user_id": "2ef2a1f6-a0b3-43e7-99fc-69d4004bd74e",
        "user_representation": "https://waardepapieren-gemeentehoorn.commonground.nu/api/v1/brp/ingeschrevenpersonen/uuid/2ef2a1f6-a0b3-43e7-99fc-69d4004bd74e",
        "claim_data": {
            "geboorte": {
                "datum": "1986-07-17",
                "land": "Nederland",
                "plaats": "Utrecht"
            },
            "doel": "akte_van_geboorte",
            "persoon": "999999928"
        },
        "iat": 1610005424
    },
    "discipl": {
        "claimData": {
            "did:discipl:ephemeral:crt:4c86faf535029c8cf4a371813cc44cb434875b18": {
                "link:discipl:ephemeral:tEi6K3mPRmE6QRf4WvpxY1hQgGmIG7uDV85zQILQNSCnQjAZPg2mj4Fbok/BHL9C8mFJQ1tCswBHBtsu6NIESA45XnN13pE+nLD6IPOeHx2cUrObxtzsqLhAy4ZXN6eDpZDmqnb6ymELUfXu/D2n4rL/t9aD279vqjFRKgBVE5WsId9c6KEYA+76mBQUBoJr8sF7w+3oMjzKy88oW693I3Keu+cdl/9sRCyYAYIDzwmg3A6n8t9KUpsBDK1b6tNznA6qoiN9Zb4JZ7rpq6lnVpyU5pyJjD+p9DiWgIYsVauJy8WOcKfNWkeOomWez0of2o+gu9xf+VLzcX3MSiAfZA==": {
                    "geboorte": {
                        "datum": "1986-07-17",
                        "land": "Nederland",
                        "plaats": "Utrecht"
                    },
                    "doel": "akte_van_geboorte",
                    "persoon": "999999928"
                }
            }
        },
        "metadata": {
            "cert": "zuid-drecht.nl:8080"
        }
    },
    "irma": {
        "claimData": {
            "did:discipl:ephemeral:crt:4c86faf535029c8cf4a371813cc44cb434875b18": {
                "link:discipl:ephemeral:tEi6K3mPRmE6QRf4WvpxY1hQgGmIG7uDV85zQILQNSCnQjAZPg2mj4Fbok/BHL9C8mFJQ1tCswBHBtsu6NIESA45XnN13pE+nLD6IPOeHx2cUrObxtzsqLhAy4ZXN6eDpZDmqnb6ymELUfXu/D2n4rL/t9aD279vqjFRKgBVE5WsId9c6KEYA+76mBQUBoJr8sF7w+3oMjzKy88oW693I3Keu+cdl/9sRCyYAYIDzwmg3A6n8t9KUpsBDK1b6tNznA6qoiN9Zb4JZ7rpq6lnVpyU5pyJjD+p9DiWgIYsVauJy8WOcKfNWkeOomWez0of2o+gu9xf+VLzcX3MSiAfZA==": {
                    "geboorte": {
                        "datum": "1986-07-17",
                        "land": "Nederland",
                        "plaats": "Utrecht"
                    },
                    "doel": "akte_van_geboorte",
                    "persoon": "999999928"
                }
            }
        },
        "metadata": {
            "cert": "zuid-drecht.nl:8080"
        }
    },
    "image": "data:image/png;base64,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",
    "document": "data:application/pdf;base64,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IRaz+40cLlhz+7Q4roD0lyLQ3yLAPb0fOxacSwFjQh49b+Sw7wuLJ04fU4V5jXrjAFnWjPtIMN+O9ZAeMAj9xpSEJLQQi+mR0JG7fnAGNvZwI19pFYSZkHMqkNClA9jelqUWK0WcuhKjA+RdX7CMHF7yVrysy01pHdi4EkcSWojFjHsOfIdxzrHnBgfOrhG889uQw6fv7LvSuNevgKE/8I1ZCOhKzGL6xf51KJP6fCKyudK1ZgmmKlIdXZZGgeOSF1oI4aPnjRwuDjMtds9zWbKBYkarcOSGnSGAIz7w83G5nNuu9RGWwLN3zHhcdwrlG4wclRw22Z3Ls9rtJbJjkfi0z94uyx3hve7fhCS0EIvZPQMPueMGJf01DlYGq+6SrMETjlWPrFlqaOxAwxs5LFi+Z/SHBy5heShzUHTGXgEpWaKJDVDOwax4RmGgbssiNogktBCLaZDQ2MwZzN8StwEdygT17bUwy8jt6phLObNENZAeIwpyoqegmMVr4GJ5c2gue4ewMcsa1MmWMvTtxCzma90kJKGFWMziGZhlivgY8PDfvJt9gYBkVae31Xs4WwTzlcUD2AXm8XDlXYNbLCyhLN7sQ5l4NvuuetBBXbzPkZKTv9JyF5CEFmIxK2dg4FZnlMf2Js6TBmUawXbkxE2/hw8LJplpatZyEhpTaCsH8AvKXsWab8yjEsvoRuT1Ha44jyn6n4QDPD/7iVhEvCuCtyCV9yBJaCEWs3sGttgACp7RR7wQ3s4EXRaVtu2k94HEc/XYBtU8N0uEhgG84pl7i1561KXwMjuvMIv61C58Be0emAIkoYVYzIhfJ9xI/Fh605suLZ20DTDeBBawQAz/iaM3ctDAVsLeC4Dtl6RLVsr35qwws/cnWjr2omViGNINSWghFpP+ywxCiDw0AwuxGA1gIRajASzEYjSAhViMBrAQi9EAFmIxGsBCLEYDWIjFaAALsRgNYCEWowEsxGI0gIVYjAawEIvRABZiMRrAQixGA1iIxWgAC7EYDWAhFqMBLMRiNICFWIwGsBCL0QAWYjEawEIsRgNYiMVoAAuxGA1gIRajASzEYjSAhViMBrAQi9EAFmIxGsBCLEYDWIjF/A/U5eCyCmVuZHN0cmVhbQplbmRvYmoKeHJlZgowIDEyCjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwMDAwOSAwMDAwMCBuIAowMDAwMDAwMDc0IDAwMDAwIG4gCjAwMDAwMDAxMjAgMDAwMDAgbiAKMDAwMDAwMDMyMiAwMDAwMCBuIAowMDAwMDAwMzU5IDAwMDAwIG4gCjAwMDAwMDA1MzYgMDAwMDAgbiAKMDAwMDAwMDYzOSAwMDAwMCBuIAowMDAwMDAxMDMxIDAwMDAwIG4gCjAwMDAwMDExNDAgMDAwMDAgbiAKMDAwMDAwMTI0OCAwMDAwMCBuIAowMDAwMDA1OTc3IDAwMDAwIG4gCnRyYWlsZXIKPDwKL1NpemUgMTIKL1Jvb3QgMSAwIFIKL0luZm8gNSAwIFIKL0lEWzxlYmVkMzE5OWRkMTczZWNiMGViNDAxMzUwYzM2OTFkYj48ZWJlZDMxOTlkZDE3M2VjYjBlYjQwMTM1MGMzNjkxZGI+XQo+PgpzdGFydHhyZWYKMTI1NTgKJSVFT0YK"
}
```


### Adding waardepapieren
The actual rendering of the waarde papieren service is done by the [CertificateService](/api/src/Service/CertificateService.php) with use of the [Certificate](/api/src/Entity/Certificate.php) object. In order to add new waardepapieren to the service add the waardepaier type to the type enum of the [Certificate](/api/src/Entity/Certificate.php) object.

Then suply handling logic for the claim under the `createClaim` function of the [CertificateService](/api/src/Service/CertificateService.php). This is done by profiding a new case and adding the required BRP data to the claim. e.g.

```PHP
    case "akte_van_geboorte":

                if(array_key_exists('geboorte', $certificate->getPersonObject())){
                    $claimData['geboorte'] = [];
                    $claimData['geboorte']['datum'] = $certificate->getPersonObject()['geboorte']['datum']['datum'];
                    $claimData['geboorte']['land'] = $certificate->getPersonObject()['geboorte']['land']['omschrijving'];
                    $claimData['geboorte']['plaats'] = $certificate->getPersonObject()['geboorte']['plaats']['omschrijving'];
                }
                else{
                    $claimData['overlijden'] = ['indicatieGeboorte'=>false];
                }

                break;
```

As you see the certificate hepler object automatically profides you with BRP acces


### Other Repro’s
*UI*
1. [Burger interface](https://github.com/ConductionNL/waardepapieren) 
2. [Ballie interface](https://github.com/ConductionNL/waardepapieren-ballie)

*Componenten*
1. [Motorblok](https://github.com/ConductionNL/waardepapieren-service) 
2. [Register](https://github.com/ConductionNL/waardepapieren-register) 

*Libraries*
1. [PHP](https://github.com/ConductionNL/waardepapieren-php)

*Plugins*
1. [Wordpress](https://github.com/ConductionNL/waardepapieren_wordpress) 
2. [Typo3](https://github.com/ConductionNL/waardepapieren_typo3) 
3. [Drupal](https://github.com/ConductionNL/waardepapieren_drupal) 


## Credits

Information about the authors of this component can be found [here](AUTHORS.md)

This component is based on the [ICTU discipl project](https://github.com/discipl/waardepapieren)

Copyright © [Dimpact](https://www.dimpact.nl/) 2020
