# How to use KubeTemp templates.
## Creating your YAML files
You can create YAML files with variables inside, then parse them with our templater to get the final file that you can apply. This can be done by putting in the identifier on the place you want.
**Identifier:**

    $(name|default_value|description)
Only name is required, this means that you can also use:

    $(name)
Or combinations like

    $(name|default_value)
    $(name||description)
## Example of a YAML file

    ---
    apiVersion: v1
    kind: Service
    metadata:
      name: $(name|gw-service|This is the name of the service)
      namespace: $(namespace)
      labels:
        app: $(name)
    spec:
      type: NodePort
      ports:
      - port: 80
        name: web
      - port: 443
        name: web-secure
      sessionAffinity: $(sessionAffinity|ClientIP)
      selector:
        app: ($name)
