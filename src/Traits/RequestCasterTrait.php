<?php

    namespace Stahiralijan\RequestCaster\Traits;

    use Illuminate\Support\Collection;

    /**
     * Trait RequestCasterTrait
     *
     * @property array $toLowerCaseWords
     * @property array $toUpperCaseWords
     * @property array $toUCFirstWords
     * @property array $toSlugs
     * @property array $toIntegers
     * @property array $toFloats
     * @property array $toBooleans
     * @property array $toArrayFromJson
     * @package App\Http\Requests\Traits
     */
    trait RequestCasterTrait
    {
        /**
         * @param array|null $keys
         *
         * @return \Illuminate\Support\Collection
         */
        public function collection(array $keys = NULL): Collection
        {
            return Collection::make($this->all($keys));
        }

        /**
         * Dump and die all the submitted fields
         *
         * @return void
         */
        public function dd(): void
        {
            Collection::make($this->all())->dd();
        }

        /**
         * Dumps all the submitted fields
         *
         * @reutrn void
         */
        public function dump(): void
        {
            Collection::make($this->all())->dump();
        }

        /**
         * (Overridden)
         * Validate the class instance.
         *
         * @return void
         */
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

        /**
         * Called by validate() method, it maps all the methods used to perform the operations
         * @return void
         */
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
        }

        /**
         * Joins the fields of the request
         * @return void
         */
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

        /**
         * Converts the request field into a lower-case string using standard PHP function strtolower()
         * @return void
         */
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

        /**
         * Converts the request field to an upper-case string using standard PHP function strtoupper()
         * @return void
         */
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

        /**
         * Capitalizes the first word for the request field using standard PHP function ucwords()
         * @return void
         */
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

        /**
         * Slugify the request field using laravel helper function str_slug()
         * @return void
         */
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

        /**
         * Converts the request field into an integer using simple casting (int)
         * @return void
         */
        protected function castToInteger(): void
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

        /**
         * Converts the request field into floating-point value using simple casting (float)
         * @return void
         */
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

        /**
         * Converts the request field into boolean using simple casting (bool)
         * @return void
         */
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

        /**
         * Converts JSON to an associated array using jscon_decode
         * @return void
         */
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
