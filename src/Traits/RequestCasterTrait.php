<?php

    namespace Stahiralijan\RequestCaster\Traits;

    use Illuminate\Support\Collection;

    trait RequestCasterTrait
    {
        /**
         * @param array|null $keys
         *
         * @return \Illuminate\Support\Collection
         */
        public function collection(array $keys = NULL)
        {
            return Collection::make($this->all($keys));
        }

        public function validate()
        {
            $this->prepareForValidation();

            $instance = $this->getValidatorInstance();

            if (!$this->passesAuthorization())
            {
                $this->failedAuthorization();
            } else if (!$instance->passes())
            {
                $this->failedValidation($instance);
            }

            if ($instance->passes())
            {
                $this->mapCasts();
            }
        }

        public function mapCasts(): void
        {
            $this->castToLowerCaseWords();
            $this->castToUpperCaseWords();
            $this->castUCFirstWords();
            $this->castToSlugs();
            $this->castToInteger();
            $this->castToFloats();
            $this->castToBoolean();
            $this->castJsonToArray();

            // run this in last so if there are fields that need to be run first should run first
            // i.e. names should be capitalized before joined together
            $this->castJoinFields();
            $this->makeNewFields();
        }
        
       protected function makeNewFields() : void
        {
            if (property_exists($this, 'newFields') && $this->newFields)
            {
                foreach ($this->newFields as $newFieldName => $value)
                {
                    $submittedFieldAndMethod = explode('|', $value);
                    $field = $submittedFieldAndMethod[0];
                    if(count($submittedFieldAndMethod) < 2)
                    {
                        $this->request->set($newFieldName, request($field));
                    }
                    $newValue = request($field);
                    $methods = array_map('trim', explode(',',$submittedFieldAndMethod[1]));
                    foreach($methods as $method)
                    {
                        $newValue = $method($newValue);
                    }
                    $this->request->set($newFieldName, $newValue);
                }
            }
        }

        protected function castJoinFields(): void
        {
            if (property_exists($this, 'joinStrings') && $this->joinStrings)
            {
                foreach ($this->joinStrings as $newFieldName => $value)
                {
                    $newFields = explode('|', $value);
                    $glue = $newFields[0];
                    $newFields = explode(',', $newFields[1]);
                    if ($newFields)
                    {
                        $joinedField = implode($glue, $this->all(array_values($newFields)));
                        $this->request->set($newFieldName, $joinedField);
                    }
                }
            }
        }

        protected function castToLowerCaseWords(): void
        {
            if (property_exists($this, 'toLowerCaseWords') && $this->toLowerCaseWords)
            {
                foreach ($this->toLowerCaseWords as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, strtolower(request($key)));
                    }
                }
            }
        }

        protected function castToUpperCaseWords(): void
        {
            if (property_exists($this, 'toUpperCaseWords') && $this->toUpperCaseWords)
            {
                foreach ($this->toUpperCaseWords as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, strtoupper(request($key)));
                    }
                }
            }
        }

        protected function castUCFirstWords(): void
        {
            if (property_exists($this, 'toUCFirstWords') && $this->toUCFirstWords)
            {
                foreach ($this->toUCFirstWords as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, ucwords(request($key)));
                    }
                }
            }
        }

        protected function castToSlugs(): void
        {
            if (property_exists($this, 'toSlugs') && $this->toSlugs)
            {
                foreach ($this->toSlugs as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, str_slug(request($key)));
                    }
                }
            }
        }

        protected function castToInteger()
        {
            if (property_exists($this, 'toIntegers') && $this->toIntegers)
            {
                foreach ($this->toIntegers as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, (int)request($key));
                    }
                }
            }
        }

        protected function castToFloats(): void
        {
            if (property_exists($this, 'toFloats') && $this->toFloats)
            {
                foreach ($this->toFloats as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, (float)request($key));
                    }
                }
            }
        }

        protected function castToBoolean(): void
        {
            if (property_exists($this, 'toBooleans') && $this->toBooleans)
            {
                foreach ($this->toBooleans as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, (bool)request($key));
                    }
                }
            }
        }

        protected function castJsonToArray(): void
        {
            if (property_exists($this, 'toArrayFromJson') && $this->toArrayFromJson)
            {
                foreach ($this->toArrayFromJson as $key)
                {
                    if ($this->request->has($key))
                    {
                        $this->request->set($key, json_decode(request($key)));
                    }
                }
            }
        }
    }
