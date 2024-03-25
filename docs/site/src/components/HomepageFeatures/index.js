import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: 'Efficient Object Mapping',
    Svg: require('@site/static/img/undraw_docusaurus_mountain.svg').default,
    description: (
      <>
          PHP AutoMapper provides a simple and efficient way to map objects of different types to each other.
          This is particularly useful when transforming data models to DTOs (Data Transfer Objects).
      </>
    ),
  },
  {
    title: 'Flexible Configuration',
    Svg: require('@site/static/img/undraw_docusaurus_tree.svg').default,
    description: (
      <>
          Configure how source objects should be mapped to destination objects.
          This includes specifying custom mapping logic for individual properties or using type converters for complex transformations.
      </>
    ),
  },
  {
    title: 'Support for Collections',
    Svg: require('@site/static/img/undraw_docusaurus_react.svg').default,
    description: (
      <>
          It handles collections of objects out of the box. Map an array or an iterable of source objects
          to an array or an iterable of destination objects with a single command.
      </>
    ),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} role="img" />
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
