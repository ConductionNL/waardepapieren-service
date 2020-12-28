Readme
=======

Welcome to the claim service! This component provides a convenience style api for creating and validating claims based on the ictu discpli project.

The service will first receive the data set linked to the provided person Uri.
This has to be either an brp uri or haal centraal uri.

Based on the type specified in the post the claim service will then fill the claim with the required data from the person object.
And then provides the claim as a JSON object and as a JWT token.

It also generates a QR code in base64 format containing the claim.

Credits
-------

Created by [Ruben van der Linde](https://www.conduction.nl/team) for conduction. But based on [discipl](https://discipl.nl/) by [ictu](https://ictu.nl/). Commercial support for common ground components available from [Conduction](https://www.conduction.nl).
